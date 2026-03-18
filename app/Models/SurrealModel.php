<?php

namespace App\Models;

use App\Services\Surreal\SurrealDocumentStore;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class SurrealModel extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function save(array $options = []): bool
    {
        $wasCreating = ! $this->exists;

        $this->mergeTimestampAttributes();

        if (! $this->getKey()) {
            $this->setAttribute($this->getKeyName(), $this->newSurrealId());
        }

        $record = $this->exists
            ? $this->store()->update($this->getTable(), (string) $this->getKey(), $this->attributesForPersistence())
            : $this->store()->create($this->getTable(), $this->attributesForPersistence());

        $this->exists = true;
        $this->wasRecentlyCreated = $wasCreating;
        $this->setRawAttributes($record, true);

        return true;
    }

    public function delete(): ?bool
    {
        if (! $this->exists || ! $this->getKey()) {
            return false;
        }

        $this->store()->delete($this->getTable(), (string) $this->getKey());
        $this->exists = false;

        return true;
    }

    public static function create(array $attributes = []): static
    {
        $model = new static;
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * @param  array<int, string>  $columns
     */
    public static function all($columns = ['*']): EloquentCollection
    {
        return new EloquentCollection(array_map(
            static fn (array $record) => static::newFromRecord($record),
            app(SurrealDocumentStore::class)->all((new static)->getTable()),
        ));
    }

    /**
     * @param  array<int, string>  $columns
     */
    public static function find($id, $columns = ['*']): ?static
    {
        $record = app(SurrealDocumentStore::class)->find((new static)->getTable(), (string) $id);

        return $record === null ? null : static::newFromRecord($record);
    }

    public function refresh(): static
    {
        $record = static::find((string) $this->getKey());

        if ($record === null) {
            return $this;
        }

        $this->setRawAttributes($record->getAttributes(), true);
        $this->exists = true;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected static function newFromRecord(array $record): static
    {
        $model = new static;
        $model->exists = true;
        $model->setRawAttributes($record, true);

        return $model;
    }

    protected function newSurrealId(): string
    {
        return sprintf('%s:%s', $this->getTable(), Str::ulid());
    }

    /**
     * @return array<string, mixed>
     */
    protected function attributesForPersistence(): array
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $this->attributesToArray();

        return $attributes;
    }

    protected function mergeTimestampAttributes(): void
    {
        if (! $this->usesTimestamps()) {
            return;
        }

        $timestamp = $this->freshTimestampString();

        if (! $this->exists && ! $this->getAttribute($this->getCreatedAtColumn())) {
            $this->setCreatedAt($timestamp);
        }

        $this->setUpdatedAt($timestamp);
    }

    protected function store(): SurrealDocumentStore
    {
        return app(SurrealDocumentStore::class);
    }
}
