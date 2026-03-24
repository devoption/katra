<?php

namespace App\Services\Surreal\Schema;

use App\Services\Surreal\Query\SurrealQueryBuilder;
use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealQueryException;
use App\Services\Surreal\SurrealRuntimeManager;
use Carbon\CarbonImmutable;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\Grammar as QueryGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Arr;
use JsonException;
use RuntimeException;

class SurrealSchemaConnection extends Connection
{
    private const SEQUENCE_TABLE = '__katra_sequences';

    /**
     * @var list<string>
     */
    private const SUPPORTED_WHERE_OPERATORS = [
        '=',
        '!=',
        '<>',
        '<',
        '>',
        '<=',
        '>=',
        'LIKE',
        'NOT LIKE',
    ];

    private ?SurrealSchemaBuilder $schemaBuilder = null;

    public function __construct(
        array $config,
        private readonly SurrealSchemaManager $manager,
        private readonly SurrealRuntimeManager $runtimeManager,
        string $name,
    ) {
        $config['name'] = $name;

        parent::__construct(
            function (): never {
                throw new RuntimeException('SurrealSchemaConnection does not expose a PDO driver. Use the Surreal schema/document layers instead.');
            },
            $config['database'] ?? '',
            $config['prefix'] ?? '',
            $config,
        );
    }

    public static function fromConfig(array $config, string $name): self
    {
        $surrealConnection = SurrealConnection::fromConfig($config);
        $client = new SurrealCliClient(
            configuredBinary: isset($config['binary']) ? (string) $config['binary'] : null,
            extrasPath: isset($config['extras_path']) ? (string) $config['extras_path'] : null,
            bundledBinaryRelativePath: isset($config['bundled_binary_relative_path']) ? (string) $config['bundled_binary_relative_path'] : null,
        );
        $httpClient = new SurrealHttpClient(app(Factory::class));
        $runtimeManager = new SurrealRuntimeManager($client, $httpClient, $surrealConnection);

        return tap(new self(
            $config,
            new SurrealSchemaManager(
                $httpClient,
                $surrealConnection,
                $runtimeManager,
            ),
            $runtimeManager,
            $name,
        ), function (self $connection): void {
            $connection->useDefaultSchemaGrammar();
        });
    }

    public function query(): SurrealQueryBuilder
    {
        return new SurrealQueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor(),
        );
    }

    public function statement($query, $bindings = []): bool
    {
        $this->ensureNoBindings($bindings);
        $this->runSurrealQuery((string) $query);

        $this->recordsHaveBeenModified();

        return true;
    }

    public function getDriverName(): string
    {
        return 'surreal';
    }

    public function select($query, $bindings = [], $useReadPdo = true, array $fetchUsing = []): array
    {
        $this->ensureNoBindings($bindings);

        return array_map(
            static fn (array $row): object => (object) $row,
            $this->normalizeRecordSet(
                Arr::get($this->runSurrealQuery((string) $query), '0', []),
                null,
                ['*'],
            ),
        );
    }

    public function cursor($query, $bindings = [], $useReadPdo = true, array $fetchUsing = []): \Generator
    {
        foreach ($this->select($query, $bindings, $useReadPdo, $fetchUsing) as $record) {
            yield $record;
        }
    }

    public function insert($query, $bindings = []): bool
    {
        return $this->statement($query, $bindings);
    }

    public function update($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function delete($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function affectingStatement($query, $bindings = []): int
    {
        $this->ensureNoBindings($bindings);

        $rows = $this->normalizeRecordSet(
            Arr::get($this->runSurrealQuery((string) $query), '0', []),
        );

        $this->recordsHaveBeenModified($rows !== []);

        return count($rows);
    }

    public function transaction(Closure $callback, $attempts = 1): mixed
    {
        throw $this->unsupportedOperation('transactions');
    }

    public function beginTransaction(): void
    {
        throw $this->unsupportedOperation('transactions');
    }

    public function commit(): void
    {
        throw $this->unsupportedOperation('transactions');
    }

    public function rollBack($toLevel = null): void
    {
        throw $this->unsupportedOperation('transactions');
    }

    public function unprepared($query): bool
    {
        return $this->statement((string) $query);
    }

    public function getSchemaBuilder(): SurrealSchemaBuilder
    {
        if ($this->schemaGrammar === null) {
            $this->useDefaultSchemaGrammar();
        }

        return $this->schemaBuilder ??= new SurrealSchemaBuilder($this, $this->manager);
    }

    public function schemaManager(): SurrealSchemaManager
    {
        return $this->manager;
    }

    public function runtimeManager(): SurrealRuntimeManager
    {
        return $this->runtimeManager;
    }

    /**
     * @param  list<string>  $columns
     * @param  array<int, array<string, mixed>>  $wheres
     * @param  array<int, array<string, mixed>>  $orders
     * @return list<array<string, mixed>>
     */
    public function selectRecords(string $table, array $columns, array $wheres = [], array $orders = [], ?int $limit = null, ?int $offset = null): array
    {
        $query = sprintf(
            'SELECT %s FROM %s',
            $this->compileSelectColumns($columns),
            $this->normalizeIdentifier($table),
        );
        $whereClause = $this->compileWhereClause($table, $wheres);

        if ($whereClause !== null) {
            $query .= ' WHERE '.$whereClause;
        }

        $orderClause = $this->compileOrderClause($orders);

        if ($orderClause !== null) {
            $query .= ' ORDER BY '.$orderClause;
        }

        if ($limit !== null) {
            $query .= ' LIMIT '.max(0, $limit);
        }

        if ($offset !== null) {
            $query .= ' START '.max(0, $offset);
        }

        $query .= ';';

        return $this->normalizeRecordSet(
            Arr::get($this->runSurrealQuery($query), '0', []),
            $table,
            $columns,
        );
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function insertRecord(string $table, array $values): array
    {
        $key = $this->keyForInsert($table, $values);

        return $this->createRecord(
            table: $table,
            key: $key,
            values: Arr::except($values, ['id']),
        );
    }

    public function insertRecordAndReturnId(string $table, array $values, string $keyName = 'id'): int|string
    {
        $key = $values[$keyName] ?? $this->keyForInsert($table, $values);

        $this->createRecord(
            table: $table,
            key: $key,
            values: Arr::except($values, [$keyName]),
        );

        return $key;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function insertOrIgnoreRecord(string $table, array $values): bool
    {
        try {
            $this->insertRecord($table, $values);

            return true;
        } catch (SurrealQueryException $exception) {
            if ($exception->isDuplicateRecord()) {
                return false;
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  list<string>  $uniqueBy
     * @param  list<string>  $updateColumns
     */
    public function upsertRecord(string $table, array $values, array $uniqueBy, array $updateColumns): bool
    {
        $match = Arr::only($values, $uniqueBy);

        if (count($match) !== count($uniqueBy)) {
            throw new RuntimeException(sprintf(
                'Unable to upsert into [%s] because one or more unique columns are missing from the payload.',
                $table,
            ));
        }

        $existingRecordId = $this->firstMatchingRecordId($table, $match);

        if ($existingRecordId === null) {
            $this->insertRecord($table, $values);

            return true;
        }

        $updatePayload = Arr::only($values, $updateColumns);

        if ($updatePayload === []) {
            return true;
        }

        $this->updateRecords($table, $updatePayload, [[
            'type' => 'Basic',
            'column' => 'id',
            'operator' => '=',
            'value' => $existingRecordId,
            'boolean' => 'and',
        ]]);

        return true;
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<int, array<string, mixed>>  $wheres
     */
    public function updateRecords(string $table, array $values, array $wheres = [], ?int $limit = null): int
    {
        if ($values === []) {
            return 0;
        }

        $recordKey = $this->recordKeyFromWheres($table, $wheres);

        if ($recordKey !== null) {
            $query = sprintf(
                'UPDATE %s MERGE %s;',
                $this->recordSelector($table, $recordKey),
                $this->encodeMap($values),
            );

            return count($this->normalizeRecordSet(Arr::get($this->runSurrealQuery($query), '0', []), $table));
        }

        $whereClause = $this->compileWhereClause($table, $wheres);

        if ($whereClause === null) {
            throw new RuntimeException('Surreal updates without a where clause are not supported by this driver yet.');
        }

        $query = sprintf(
            'UPDATE %s WHERE %s MERGE %s%s;',
            $this->normalizeIdentifier($table),
            $whereClause,
            $this->encodeMap($values),
            $limit !== null ? ' LIMIT '.max(0, $limit) : '',
        );

        return count($this->normalizeRecordSet(Arr::get($this->runSurrealQuery($query), '0', []), $table));
    }

    /**
     * @param  array<int, array<string, mixed>>  $wheres
     */
    public function deleteRecords(string $table, array $wheres = [], ?int $limit = null): int
    {
        $recordKey = $this->recordKeyFromWheres($table, $wheres);

        if ($recordKey !== null) {
            $query = sprintf('DELETE %s;', $this->recordSelector($table, $recordKey));

            return count($this->normalizeRecordSet(Arr::get($this->runSurrealQuery($query), '0', []), $table));
        }

        $whereClause = $this->compileWhereClause($table, $wheres);

        if ($whereClause === null) {
            throw new RuntimeException('Surreal deletes without a where clause are not supported by this driver yet.');
        }

        $query = sprintf(
            'DELETE %s WHERE %s%s;',
            $this->normalizeIdentifier($table),
            $whereClause,
            $limit !== null ? ' LIMIT '.max(0, $limit) : '',
        );

        return count($this->normalizeRecordSet(Arr::get($this->runSurrealQuery($query), '0', []), $table));
    }

    protected function getDefaultSchemaGrammar(): SurrealSchemaGrammar
    {
        return new SurrealSchemaGrammar($this);
    }

    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        return new QueryGrammar($this);
    }

    protected function getDefaultPostProcessor(): Processor
    {
        return new Processor;
    }

    private function unsupportedOperation(string $operation): RuntimeException
    {
        return new RuntimeException(sprintf(
            'SurrealSchemaConnection does not support %s. Use the Surreal document layer for data access.',
            $operation,
        ));
    }

    /**
     * @return list<mixed>
     */
    private function runSurrealQuery(string $query): array
    {
        if (! $this->runtimeManager->ensureReady()) {
            throw new RuntimeException('The SurrealDB runtime is not available for query operations.');
        }

        return app(SurrealHttpClient::class)->runQuery(
            endpoint: (string) $this->getConfig('endpoint'),
            namespace: (string) $this->getConfig('namespace'),
            database: (string) $this->getConfig('database'),
            username: (string) $this->getConfig('username'),
            password: (string) $this->getConfig('password'),
            query: $query,
        );
    }

    /**
     * @param  array<int, mixed>  $bindings
     */
    private function ensureNoBindings(array $bindings): void
    {
        if ($bindings !== []) {
            throw new RuntimeException('Parameterized bindings are not supported on the current Surreal raw query path yet.');
        }
    }

    /**
     * @param  list<string>  $columns
     * @return list<array<string, mixed>>
     */
    private function normalizeRecordSet(mixed $statement, ?string $table = null, array $columns = ['*']): array
    {
        if (! is_array($statement)) {
            return [];
        }

        $values = array_values($statement);

        if ($values === []) {
            return [];
        }

        $firstValue = $values[0];

        if (is_array($firstValue) && $this->isAssociative($firstValue)) {
            return array_map(
                fn (array $record): array => $this->normalizeRecord($record, $table, $columns),
                $values,
            );
        }

        if (is_array($firstValue) && ! $this->isAssociative($firstValue)) {
            return array_map(
                fn (array $record): array => $this->normalizeRecord($record, $table, $columns),
                $firstValue,
            );
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  list<string>  $columns
     * @return array<string, mixed>
     */
    private function normalizeRecord(array $record, ?string $table, array $columns): array
    {
        if (isset($record['id']) && is_string($record['id']) && $table !== null) {
            $record['id'] = $this->extractRecordKey($table, $record['id']);
        }

        if ($columns === ['*']) {
            return $record;
        }

        return Arr::only($record, $columns);
    }

    /**
     * @param  array<int, array<string, mixed>>  $wheres
     */
    private function compileWhereClause(string $table, array $wheres): ?string
    {
        if ($wheres === []) {
            return null;
        }

        $segments = [];

        foreach ($wheres as $index => $where) {
            $boolean = strtoupper((string) ($where['boolean'] ?? 'and'));
            $prefix = $index === 0 ? '' : $boolean.' ';

            $segments[] = $prefix.$this->compileWhereSegment($table, $where);
        }

        return implode(' ', $segments);
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function compileWhereSegment(string $table, array $where): string
    {
        return match ($where['type'] ?? null) {
            'Basic' => $this->compileBasicWhere($table, $where),
            'Nested' => $this->compileNestedWhere($table, $where),
            'Null' => sprintf('%s = NONE', $this->normalizeColumn((string) $where['column'])),
            'NotNull' => sprintf('%s != NONE', $this->normalizeColumn((string) $where['column'])),
            'In' => $this->compileInWhere($table, $where, false),
            'NotIn' => $this->compileInWhere($table, $where, true),
            default => throw new RuntimeException(sprintf(
                'The current Surreal query driver does not support [%s] where clauses yet.',
                (string) ($where['type'] ?? 'unknown'),
            )),
        };
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function compileBasicWhere(string $table, array $where): string
    {
        $column = $this->normalizeColumn((string) $where['column']);
        $operator = $this->normalizeOperator((string) ($where['operator'] ?? '='));
        $value = $where['value'] ?? null;

        $encodedValue = $column === 'id'
            ? $this->recordSelector($table, $value)
            : $this->encodeLiteral($value);

        return sprintf('%s %s %s', $column, $operator, $encodedValue);
    }

    /**
     * @param  list<string>  $columns
     */
    private function compileSelectColumns(array $columns): string
    {
        if ($columns === ['*']) {
            return '*';
        }

        return implode(', ', array_map(function (string $column): string {
            if ($column === '*') {
                return '*';
            }

            return $this->normalizeColumn($column);
        }, $columns));
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function compileNestedWhere(string $table, array $where): string
    {
        $nestedWheres = $where['query']->wheres ?? [];
        $compiled = $this->compileWhereClause($table, $nestedWheres);

        if ($compiled === null) {
            return 'true';
        }

        return '('.$compiled.')';
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function compileInWhere(string $table, array $where, bool $negated): string
    {
        $values = array_values(array_filter(
            $where['values'] ?? [],
            static fn (mixed $value): bool => $value !== null,
        ));

        if ($values === []) {
            return $negated ? 'true' : 'false';
        }

        $column = $this->normalizeColumn((string) $where['column']);
        $comparisonOperator = $negated ? '!=' : '=';

        $segments = array_map(function (mixed $value) use ($column, $comparisonOperator, $table): string {
            $encodedValue = $column === 'id'
                ? $this->recordSelector($table, $value)
                : $this->encodeLiteral($value);

            return sprintf('%s %s %s', $column, $comparisonOperator, $encodedValue);
        }, $values);

        return '('.implode($negated ? ' AND ' : ' OR ', $segments).')';
    }

    /**
     * @param  array<int, array<string, mixed>>  $orders
     */
    private function compileOrderClause(array $orders): ?string
    {
        if ($orders === []) {
            return null;
        }

        $segments = [];

        foreach ($orders as $order) {
            if (($order['type'] ?? 'Basic') !== 'Basic') {
                throw new RuntimeException('The current Surreal query driver only supports basic order clauses.');
            }

            $segments[] = sprintf(
                '%s %s',
                $this->normalizeColumn((string) $order['column']),
                strtoupper((string) ($order['direction'] ?? 'asc')),
            );
        }

        return implode(', ', $segments);
    }

    private function normalizeOperator(string $operator): string
    {
        $normalized = strtoupper(trim($operator));

        if (! in_array($normalized, self::SUPPORTED_WHERE_OPERATORS, true)) {
            throw new RuntimeException(sprintf(
                'The current Surreal query driver does not support the [%s] operator.',
                $operator,
            ));
        }

        return $normalized;
    }

    /**
     * @param  array<int, array<string, mixed>>  $wheres
     */
    private function recordKeyFromWheres(string $table, array $wheres): mixed
    {
        foreach ($wheres as $where) {
            if (($where['type'] ?? null) !== 'Basic') {
                continue;
            }

            if ($this->normalizeColumn((string) $where['column']) !== 'id') {
                continue;
            }

            if ((string) ($where['operator'] ?? '=') !== '=') {
                continue;
            }

            return $this->extractRecordKey($table, (string) $this->normalizeRecordIdentifier($table, $where['value']));
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function createRecord(string $table, mixed $key, array $values): array
    {
        $query = sprintf(
            'CREATE ONLY %s CONTENT %s;',
            $this->recordSelector($table, $key),
            $this->encodeMap($values),
        );

        return $this->normalizeRecordSet(
            Arr::get($this->runSurrealQuery($query), '0', []),
            $table,
        )[0] ?? throw new RuntimeException(sprintf('Failed to create the Surreal record for table [%s].', $table));
    }

    private function nextKey(string $table): int
    {
        $result = Arr::get($this->runSurrealQuery(sprintf(
            'UPSERT ONLY %s SET value += 1 RETURN VALUE value;',
            $this->recordSelector(self::SEQUENCE_TABLE, $table),
        )), '0.0');

        if (! is_int($result) && ! ctype_digit((string) $result)) {
            throw new RuntimeException(sprintf(
                'Unable to generate the next numeric id for table [%s].',
                $table,
            ));
        }

        return (int) $result;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function keyForInsert(string $table, array $values): int|string
    {
        if (array_key_exists('id', $values)) {
            return $values['id'];
        }

        if (array_key_exists('key', $values) && is_scalar($values['key'])) {
            return (string) $values['key'];
        }

        return $this->nextKey($table);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function firstMatchingRecordId(string $table, array $values): int|string|null
    {
        $wheres = array_map(
            static fn (string $column, mixed $value): array => [
                'type' => 'Basic',
                'column' => $column,
                'operator' => '=',
                'value' => $value,
                'boolean' => 'and',
            ],
            array_keys($values),
            array_values($values),
        );

        $record = $this->selectRecords(
            table: $table,
            columns: ['id'],
            wheres: $wheres,
            limit: 1,
        )[0] ?? null;

        if (! is_array($record) || ! array_key_exists('id', $record)) {
            return null;
        }

        return $record['id'];
    }

    private function normalizeIdentifier(string $identifier): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new RuntimeException(sprintf('The Surreal identifier [%s] contains unsupported characters.', $identifier));
        }

        return $identifier;
    }

    private function normalizeColumn(string $column): string
    {
        $column = str_contains($column, '.') ? (string) last(explode('.', $column)) : $column;

        return $this->normalizeIdentifier($column);
    }

    private function normalizeRecordIdentifier(string $table, mixed $value): string
    {
        if (is_string($value) && str_starts_with($value, $table.':')) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return sprintf('%s:%s', $table, $value);
        }

        if (is_int($value) || is_float($value) || (is_string($value) && ctype_digit($value))) {
            return sprintf('%s:%s', $table, (string) $value);
        }

        throw new RuntimeException(sprintf('The Surreal record id [%s] contains unsupported characters.', (string) $value));
    }

    private function recordSelector(string $table, mixed $value): string
    {
        $recordIdentifier = $this->normalizeRecordIdentifier($table, $value);
        [$recordTable, $recordKey] = explode(':', $recordIdentifier, 2);

        $keyLiteral = ctype_digit($recordKey)
            ? $recordKey
            : $this->encodeLiteral($recordKey);

        return sprintf(
            'type::record(%s, %s)',
            $this->encodeLiteral($recordTable),
            $keyLiteral,
        );
    }

    private function extractRecordKey(string $table, string $recordId): int|string
    {
        $normalizedRecordId = preg_replace('/^([A-Za-z0-9_]+):`(.+)`$/', '$1:$2', $recordId) ?? $recordId;
        $prefix = $this->normalizeIdentifier($table).':';

        if (! str_starts_with($normalizedRecordId, $prefix)) {
            return $normalizedRecordId;
        }

        $key = substr($normalizedRecordId, strlen($prefix));

        return ctype_digit($key) ? (int) $key : $key;
    }

    private function encodeLiteral(mixed $value): string
    {
        if ($value === null) {
            return 'NONE';
        }

        if (is_array($value) && $this->isAssociative($value)) {
            return $this->encodeMap($value);
        }

        try {
            $encoded = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode the Surreal query payload.', previous: $exception);
        }

        if (! is_string($encoded)) {
            throw new RuntimeException('Unable to encode the Surreal query payload.');
        }

        return $encoded;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function encodeMap(array $values): string
    {
        $segments = [];

        foreach ($values as $key => $value) {
            $segments[] = sprintf(
                '%s: %s',
                $this->normalizeColumn((string) $key),
                $this->encodeValue($key, $value),
            );
        }

        return '{'.implode(', ', $segments).'}';
    }

    private function encodeValue(string|int $key, mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $this->encodeDateTimeLiteral(
                CarbonImmutable::instance($value)->utc()->format(DATE_ATOM),
            );
        }

        if (is_string($value) && is_string($key) && $this->looksLikeDateTimeColumn($key)) {
            try {
                return $this->encodeDateTimeLiteral(
                    CarbonImmutable::parse($value, config('app.timezone'))->utc()->format(DATE_ATOM),
                );
            } catch (\Throwable) {
                // Fall through to the generic JSON literal path when the value is not a real datetime string.
            }
        }

        if (is_array($value) && $this->isAssociative($value)) {
            return $this->encodeMap($value);
        }

        return $this->encodeLiteral($value);
    }

    private function encodeDateTimeLiteral(string $value): string
    {
        return sprintf("d'%s'", $value);
    }

    private function looksLikeDateTimeColumn(string $column): bool
    {
        return str_ends_with($column, '_at');
    }

    /**
     * @param  array<mixed>  $value
     */
    private function isAssociative(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }
}
