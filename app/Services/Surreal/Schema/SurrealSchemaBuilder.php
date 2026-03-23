<?php

namespace App\Services\Surreal\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

class SurrealSchemaBuilder extends Builder
{
    public function __construct(
        Connection $connection,
        private readonly SurrealSchemaManager $manager,
    ) {
        parent::__construct($connection);
    }

    /**
     * @return list<array{name: string, schema: string|null, schema_qualified_name: string, size: int|null, comment: string|null, collation: string|null, engine: string|null}>
     */
    public function getTables($schema = null): array
    {
        return $this->manager->tables();
    }

    public function hasTable($table): bool
    {
        [, $table] = $this->parseSchemaAndTable($table);

        return $this->manager->hasTable($this->connection->getTablePrefix().$table);
    }

    /**
     * @return list<array{name: string, type: string, type_name: string, nullable: bool, default: mixed, auto_increment: bool, comment: string|null, generation: array{type: string, expression: string|null}|null}>
     */
    public function getColumns($table): array
    {
        [, $table] = $this->parseSchemaAndTable($table);

        return $this->manager->columns($this->connection->getTablePrefix().$table);
    }

    public function dropIfExists($table): void
    {
        if ($this->hasTable($table)) {
            $this->drop($table);
        }
    }

    public function dropAllTables(): void
    {
        $this->manager->dropAllTables();
    }

    protected function build(Blueprint $blueprint): void
    {
        $this->manager->statements(array_map(
            static fn (mixed $statement): string => (string) $statement,
            $blueprint->toSql(),
        ));
    }
}
