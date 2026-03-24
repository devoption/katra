<?php

namespace App\Services\Surreal\Query;

use App\Services\Surreal\Schema\SurrealSchemaConnection;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;
use stdClass;

class SurrealQueryBuilder extends Builder
{
    /**
     * @param  string|Expression|array<string|Expression>  $columns
     * @return Collection<int, stdClass>
     */
    public function get($columns = ['*']): Collection
    {
        $original = $this->columns;

        $this->columns ??= Arr::wrap($columns);

        $rows = $this->surrealConnection()->selectRecords(
            table: (string) $this->from,
            columns: $this->resolveColumns($this->columns),
            wheres: $this->wheres ?? [],
            orders: $this->orders ?? [],
            limit: $this->limit,
            offset: $this->offset,
        );

        $this->columns = $original;

        return $this->applyAfterQueryCallbacks(new Collection(array_map(
            static fn (array $row): stdClass => (object) $row,
            $rows,
        )));
    }

    public function insert(array $values): bool
    {
        $records = $this->prepareInsertValues($values);

        foreach ($records as $record) {
            $this->surrealConnection()->insertRecord(
                table: (string) $this->from,
                values: $record,
            );
        }

        return true;
    }

    public function insertGetId(array $values, $sequence = null): int|string
    {
        return $this->surrealConnection()->insertRecordAndReturnId(
            table: (string) $this->from,
            values: $values,
            keyName: is_string($sequence) && $sequence !== '' ? $sequence : 'id',
        );
    }

    public function update(array $values): int
    {
        if ($values === []) {
            return 0;
        }

        return $this->surrealConnection()->updateRecords(
            table: (string) $this->from,
            values: $values,
            wheres: $this->wheres ?? [],
            limit: $this->limit,
        );
    }

    public function delete($id = null): int
    {
        $query = $this;

        if ($id !== null) {
            $query = clone $this;
            $query->where('id', '=', $id);
        }

        return $this->surrealConnection()->deleteRecords(
            table: (string) $query->from,
            wheres: $query->wheres ?? [],
            limit: $query->limit,
        );
    }

    public function exists(): bool
    {
        $query = clone $this;

        return $query->limit(1)->get(['id'])->isNotEmpty();
    }

    public function count($columns = '*'): int
    {
        return $this->get(Arr::wrap($columns))->count();
    }

    /**
     * @param  array<int|string, mixed>  $values
     * @return list<array<string, mixed>>
     */
    private function prepareInsertValues(array $values): array
    {
        if ($values === []) {
            return [];
        }

        if (array_is_list($values) && isset($values[0]) && is_array($values[0])) {
            return array_values(array_map(
                static function (mixed $record): array {
                    if (! is_array($record)) {
                        throw new RuntimeException('Surreal bulk inserts expect each record payload to be an array.');
                    }

                    /** @var array<string, mixed> $record */
                    return $record;
                },
                $values,
            ));
        }

        /** @var array<string, mixed> $values */
        return [$values];
    }

    /**
     * @param  array<int, string|Expression>  $columns
     * @return list<string>
     */
    private function resolveColumns(array $columns): array
    {
        return array_values(array_map(function (mixed $column): string {
            if ($column instanceof Expression) {
                return (string) $column->getValue($this->grammar);
            }

            return (string) $column;
        }, $columns));
    }

    private function surrealConnection(): SurrealSchemaConnection
    {
        $connection = $this->getConnection();

        if (! $connection instanceof SurrealSchemaConnection) {
            throw new RuntimeException('SurrealQueryBuilder requires a SurrealSchemaConnection instance.');
        }

        return $connection;
    }
}
