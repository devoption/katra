<?php

namespace App\Services\Surreal\Schema;

use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Support\Arr;
use RuntimeException;

class SurrealSchemaManager
{
    public function __construct(
        private readonly SurrealCliClient $client,
        private readonly SurrealConnection $connection,
        private readonly SurrealRuntimeManager $runtimeManager,
    ) {}

    public function statement(string $statement): bool
    {
        $this->run($statement);

        return true;
    }

    /**
     * @param  list<string>  $statements
     */
    public function statements(array $statements): bool
    {
        $statements = array_values(array_filter(array_map(
            static fn (string $statement): string => trim($statement),
            $statements,
        )));

        if ($statements === []) {
            return true;
        }

        $this->run(implode("\n", $statements));

        return true;
    }

    /**
     * @return list<array{name: string, schema: string|null, schema_qualified_name: string, size: int|null, comment: string|null, collation: string|null, engine: string|null}>
     */
    public function tables(): array
    {
        $tables = Arr::get($this->run('INFO FOR DB;'), '0.0.tables', []);

        if (! is_array($tables)) {
            return [];
        }

        return array_values(array_map(
            static fn (string $definition, string $name): array => [
                'name' => $name,
                'schema' => null,
                'schema_qualified_name' => $name,
                'size' => null,
                'comment' => $definition,
                'collation' => null,
                'engine' => null,
            ],
            $tables,
            array_keys($tables),
        ));
    }

    public function hasTable(string $table): bool
    {
        return collect($this->tables())->contains(
            static fn (array $definition): bool => $definition['name'] === $table
        );
    }

    /**
     * @return list<array{name: string, type: string, type_name: string, nullable: bool, default: mixed, auto_increment: bool, comment: string|null, generation: array{type: string, expression: string|null}|null}>
     */
    public function columns(string $table): array
    {
        $fields = Arr::get($this->run(sprintf('INFO FOR TABLE %s;', $this->normalizeIdentifier($table))), '0.0.fields', []);

        if (! is_array($fields)) {
            return [];
        }

        return array_values(array_map(
            fn (string $definition, string $name): array => $this->normalizeFieldDefinition($name, $definition),
            $fields,
            array_keys($fields),
        ));
    }

    public function dropAllTables(): void
    {
        $this->statements(array_map(
            fn (array $table): string => sprintf('REMOVE TABLE %s;', $this->normalizeIdentifier($table['name'])),
            $this->tables(),
        ));
    }

    /**
     * @return list<mixed>
     */
    private function run(string $query): array
    {
        if (! $this->runtimeManager->ensureReady()) {
            throw new RuntimeException('The SurrealDB runtime is not available for schema operations.');
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

    /**
     * @return array{name: string, type: string, type_name: string, nullable: bool, default: mixed, auto_increment: bool, comment: string|null, generation: array{type: string, expression: string|null}|null}
     */
    private function normalizeFieldDefinition(string $name, string $definition): array
    {
        preg_match('/TYPE\s+(.+?)\s+PERMISSIONS/i', $definition, $matches);

        $typeDefinition = $matches[1] ?? 'any';
        $nullable = str_contains($typeDefinition, 'none | ');
        $typeName = trim(str_replace('none |', '', $typeDefinition));

        return [
            'name' => $name,
            'type' => $typeDefinition,
            'type_name' => $typeName,
            'nullable' => $nullable,
            'default' => null,
            'auto_increment' => false,
            'comment' => $definition,
            'generation' => null,
        ];
    }

    private function normalizeIdentifier(string $identifier): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new RuntimeException(sprintf('The Surreal schema identifier [%s] contains unsupported characters.', $identifier));
        }

        return $identifier;
    }
}
