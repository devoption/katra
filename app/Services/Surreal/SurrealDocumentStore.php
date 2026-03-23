<?php

namespace App\Services\Surreal;

use Illuminate\Support\Arr;
use RuntimeException;

class SurrealDocumentStore
{
    public function __construct(
        private readonly SurrealHttpClient $client,
        private readonly SurrealConnection $connection,
        private readonly SurrealRuntimeManager $runtimeManager,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(string $table): array
    {
        try {
            return $this->normalizeRecordSet($this->run(sprintf('SELECT * FROM %s;', $this->normalizeTable($table)))[0] ?? []);
        } catch (RuntimeException $exception) {
            if ($this->tableMissing($exception, $table)) {
                return [];
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $table, string $id): ?array
    {
        try {
            return $this->normalizeRecordSet($this->run(sprintf('SELECT * FROM %s;', $this->recordSelector($table, $id)))[0] ?? [])[0] ?? null;
        } catch (RuntimeException $exception) {
            if ($this->tableMissing($exception, $table)) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function create(string $table, array $attributes): array
    {
        $id = $this->normalizeRecordId($table, (string) ($attributes['id'] ?? ''));
        $attributes['id'] = $id;
        $payload = Arr::except($attributes, ['id']);

        return $this->normalizeRecordSet($this->run(sprintf('CREATE ONLY %s CONTENT %s;', $this->recordSelector($table, $id), $this->encodeAttributes($payload)))[0] ?? [])[0]
            ?? throw new RuntimeException(sprintf('Failed to create the SurrealDB record [%s].', $id));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function update(string $table, string $id, array $attributes): array
    {
        $recordId = $this->normalizeRecordId($table, $id);
        $attributes['id'] = $recordId;
        $payload = Arr::except($attributes, ['id']);

        return $this->normalizeRecordSet($this->run(sprintf('UPDATE %s CONTENT %s;', $this->recordSelector($table, $recordId), $this->encodeAttributes($payload)))[0] ?? [])[0]
            ?? throw new RuntimeException(sprintf('Failed to update the SurrealDB record [%s].', $recordId));
    }

    public function delete(string $table, string $id): void
    {
        $this->run(sprintf('DELETE %s;', $this->recordSelector($table, $id)));
    }

    /**
     * @return array<int, mixed>
     */
    private function run(string $query): array
    {
        if (! $this->runtimeManager->ensureReady()) {
            throw new RuntimeException('The SurrealDB runtime is not available for the current connection.');
        }

        return $this->client->runQuery(
            endpoint: $this->connection->endpoint,
            namespace: $this->connection->namespace,
            database: $this->connection->database,
            username: $this->connection->username,
            password: $this->connection->password,
            query: $query,
        );
    }

    private function normalizeRecordId(string $table, string $id): string
    {
        $normalizedTable = $this->normalizeTable($table);

        if ($id === '') {
            throw new RuntimeException(sprintf('The SurrealDB record id for table [%s] must not be empty.', $normalizedTable));
        }

        if (str_contains($id, ':')) {
            if (! preg_match('/^[A-Za-z0-9_]+:[A-Za-z0-9_-]+$/', $id)) {
                throw new RuntimeException(sprintf('The SurrealDB record id [%s] contains unsupported characters.', $id));
            }

            return $id;
        }

        if (! preg_match('/^[A-Za-z0-9_-]+$/', $id)) {
            throw new RuntimeException(sprintf('The SurrealDB record id [%s] contains unsupported characters.', $id));
        }

        return sprintf('%s:%s', $normalizedTable, $id);
    }

    private function recordSelector(string $table, string $id): string
    {
        $recordId = $this->normalizeRecordId($table, $id);
        [$recordTable, $recordKey] = explode(':', $recordId, 2);

        return sprintf('type::record(%s, %s)', $this->encodeString($recordTable), $this->encodeString($recordKey));
    }

    private function normalizeTable(string $table): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            throw new RuntimeException(sprintf('The SurrealDB table [%s] contains unsupported characters.', $table));
        }

        return $table;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRecordSet(mixed $statement): array
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
            /** @var array<int, array<string, mixed>> $values */
            return array_map(fn (array $record): array => $this->normalizeRecord($record), $values);
        }

        if (is_array($firstValue) && ! $this->isAssociative($firstValue)) {
            /** @var array<int, array<string, mixed>> $firstValue */
            return array_map(fn (array $record): array => $this->normalizeRecord($record), $firstValue);
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function encodeAttributes(array $attributes): string
    {
        $encoded = json_encode($attributes, JSON_THROW_ON_ERROR);

        if (! is_string($encoded)) {
            throw new RuntimeException('Failed to encode the SurrealDB document payload.');
        }

        return $encoded;
    }

    private function encodeString(string $value): string
    {
        $encoded = json_encode($value, JSON_THROW_ON_ERROR);

        if (! is_string($encoded)) {
            throw new RuntimeException('Failed to encode the SurrealDB record identifier.');
        }

        return $encoded;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function normalizeRecord(array $record): array
    {
        if (isset($record['id']) && is_string($record['id'])) {
            $record['id'] = preg_replace('/^([A-Za-z0-9_]+):`(.+)`$/', '$1:$2', $record['id']) ?? $record['id'];
        }

        return $record;
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

    private function tableMissing(RuntimeException $exception, string $table): bool
    {
        return str_contains($exception->getMessage(), sprintf("table '%s' does not exist", $this->normalizeTable($table)));
    }
}
