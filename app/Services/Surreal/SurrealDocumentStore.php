<?php

namespace App\Services\Surreal;

use Illuminate\Support\Arr;
use RuntimeException;

class SurrealDocumentStore
{
    public function __construct(
        private readonly SurrealCliClient $client,
        private readonly SurrealConnection $connection,
        private readonly SurrealRuntimeManager $runtimeManager,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(string $table): array
    {
        return $this->normalizeRecordSet($this->run(sprintf('SELECT * FROM %s;', $table))[0] ?? []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $table, string $id): ?array
    {
        return $this->normalizeRecordSet($this->run(sprintf('SELECT * FROM %s;', $this->normalizeRecordId($table, $id)))[0] ?? [])[0] ?? null;
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

        return $this->normalizeRecordSet($this->run(sprintf('CREATE ONLY %s CONTENT %s;', $id, $this->encodeAttributes($payload)))[0] ?? [])[0]
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

        return $this->normalizeRecordSet($this->run(sprintf('UPDATE %s CONTENT %s;', $recordId, $this->encodeAttributes($payload)))[0] ?? [])[0]
            ?? throw new RuntimeException(sprintf('Failed to update the SurrealDB record [%s].', $recordId));
    }

    public function delete(string $table, string $id): void
    {
        $this->run(sprintf('DELETE %s;', $this->normalizeRecordId($table, $id)));
    }

    /**
     * @return array<int, mixed>
     */
    private function run(string $query): array
    {
        if (! $this->runtimeManager->ensureReady()) {
            throw new RuntimeException('The SurrealDB runtime is not available. Install the `surreal` CLI or configure a reachable SurrealDB endpoint.');
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
        if ($id === '') {
            throw new RuntimeException(sprintf('The SurrealDB record id for table [%s] must not be empty.', $table));
        }

        if (str_contains($id, ':')) {
            return $id;
        }

        return sprintf('%s:%s', $table, $id);
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
            return $values;
        }

        if (is_array($firstValue) && ! $this->isAssociative($firstValue)) {
            /** @var array<int, array<string, mixed>> $firstValue */
            return $firstValue;
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
