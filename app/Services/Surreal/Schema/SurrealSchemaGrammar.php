<?php

namespace App\Services\Surreal\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use RuntimeException;

class SurrealSchemaGrammar extends Grammar
{
    /**
     * @return list<string>
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command): array
    {
        $table = $this->normalizeIdentifier($blueprint->getTable());

        return array_merge(
            [sprintf('DEFINE TABLE %s SCHEMAFULL;', $table)],
            array_map(
                fn (ColumnDefinition $column): string => $this->compileFieldDefinition($table, $column),
                $blueprint->getColumns(),
            ),
        );
    }

    /**
     * @return list<string>
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command): array
    {
        $table = $this->normalizeIdentifier($blueprint->getTable());

        return [
            $this->compileFieldDefinition($table, $command->column),
        ];
    }

    public function compileDrop(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('REMOVE TABLE %s;', $this->normalizeIdentifier($blueprint->getTable()));
    }

    public function compileDropIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileDrop($blueprint, $command);
    }

    /**
     * @return list<string>
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command): array
    {
        $table = $this->normalizeIdentifier($blueprint->getTable());

        return array_map(
            fn (string $column): string => sprintf(
                'REMOVE FIELD %s ON TABLE %s;',
                $this->normalizeIdentifier($column),
                $table,
            ),
            $command->columns,
        );
    }

    public function compileRenameColumn(Blueprint $blueprint, Fluent $command): never
    {
        throw new RuntimeException('The current SurrealDB schema driver does not support renaming columns yet.');
    }

    public function compileForeign(Blueprint $blueprint, Fluent $command): never
    {
        throw new RuntimeException('The current SurrealDB schema driver does not support foreign key constraints.');
    }

    private function compileFieldDefinition(string $table, ColumnDefinition $column): string
    {
        return sprintf(
            'DEFINE FIELD %s ON TABLE %s TYPE %s;',
            $this->normalizeIdentifier($column->name),
            $table,
            $this->surrealTypeFor($column),
        );
    }

    private function surrealTypeFor(ColumnDefinition $column): string
    {
        $baseType = match ($column->type) {
            'bigIncrements', 'bigInteger', 'foreignId', 'unsignedBigInteger' => 'int',
            'increments', 'integer', 'mediumIncrements', 'mediumInteger', 'unsignedInteger' => 'int',
            'smallIncrements', 'smallInteger', 'unsignedSmallInteger' => 'int',
            'tinyIncrements', 'tinyInteger', 'unsignedTinyInteger' => 'int',
            'char', 'string', 'tinyText', 'text', 'mediumText', 'longText', 'ulid' => 'string',
            'float', 'double', 'decimal' => 'number',
            'boolean' => 'bool',
            'json', 'jsonb' => 'any',
            'date', 'dateTime', 'dateTimeTz', 'time', 'timeTz', 'timestamp', 'timestampTz' => 'datetime',
            'uuid' => 'uuid',
            default => throw new RuntimeException(sprintf('The current SurrealDB schema driver does not support the [%s] column type yet.', $column->type)),
        };

        return $column->nullable ? sprintf('none | %s', $baseType) : $baseType;
    }

    private function normalizeIdentifier(string $identifier): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new RuntimeException(sprintf('The Surreal schema identifier [%s] contains unsupported characters.', $identifier));
        }

        return $identifier;
    }
}
