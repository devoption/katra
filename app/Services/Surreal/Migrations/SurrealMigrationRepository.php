<?php

namespace App\Services\Surreal\Migrations;

use App\Services\Surreal\Schema\SurrealSchemaConnection;
use App\Services\Surreal\Schema\SurrealSchemaManager;
use App\Services\Surreal\SurrealHttpClient;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Support\Arr;
use JsonException;

class SurrealMigrationRepository extends DatabaseMigrationRepository
{
    public function __construct(Resolver $resolver, string $table, private readonly SurrealHttpClient $client)
    {
        parent::__construct($resolver, $table);
    }

    public function getRan()
    {
        if (! $this->usesSurrealRepository()) {
            return parent::getRan();
        }

        return array_map(
            static fn (array $row): string => (string) $row['migration'],
            $this->selectRows(sprintf(
                'SELECT migration, batch FROM %s ORDER BY batch ASC, migration ASC;',
                $this->normalizedTable(),
            )),
        );
    }

    public function getMigrations($steps)
    {
        if (! $this->usesSurrealRepository()) {
            return parent::getMigrations($steps);
        }

        return $this->selectRows(sprintf(
            'SELECT migration, batch FROM %s WHERE batch >= 1 ORDER BY batch DESC, migration DESC LIMIT %d;',
            $this->normalizedTable(),
            (int) $steps,
        ));
    }

    public function getMigrationsByBatch($batch)
    {
        if (! $this->usesSurrealRepository()) {
            return parent::getMigrationsByBatch($batch);
        }

        return $this->selectRows(sprintf(
            'SELECT migration, batch FROM %s WHERE batch = %d ORDER BY migration DESC;',
            $this->normalizedTable(),
            (int) $batch,
        ));
    }

    public function getLast()
    {
        if (! $this->usesSurrealRepository()) {
            return parent::getLast();
        }

        $lastBatch = $this->getLastBatchNumber();

        if ($lastBatch === 0) {
            return [];
        }

        return $this->getMigrationsByBatch($lastBatch);
    }

    public function getMigrationBatches()
    {
        if (! $this->usesSurrealRepository()) {
            return parent::getMigrationBatches();
        }

        $batches = [];

        foreach ($this->selectRows(sprintf(
            'SELECT migration, batch FROM %s ORDER BY batch ASC, migration ASC;',
            $this->normalizedTable(),
        )) as $row) {
            $batches[(string) $row['migration']] = (int) $row['batch'];
        }

        return $batches;
    }

    public function log($file, $batch)
    {
        if (! $this->usesSurrealRepository()) {
            parent::log($file, $batch);

            return;
        }

        $this->schemaManager()->statement(sprintf(
            'CREATE %s CONTENT %s;',
            $this->normalizedTable(),
            $this->jsonLiteral([
                'migration' => (string) $file,
                'batch' => (int) $batch,
            ]),
        ));
    }

    public function delete($migration)
    {
        if (! $this->usesSurrealRepository()) {
            parent::delete($migration);

            return;
        }

        $this->schemaManager()->statement(sprintf(
            'DELETE %s WHERE migration = %s;',
            $this->normalizedTable(),
            $this->jsonLiteral((string) $migration->migration),
        ));
    }

    public function getLastBatchNumber()
    {
        if (! $this->usesSurrealRepository()) {
            return parent::getLastBatchNumber();
        }

        return (int) $this->selectScalar(sprintf(
            'SELECT VALUE batch FROM %s ORDER BY batch DESC LIMIT 1;',
            $this->normalizedTable(),
        ), 0);
    }

    public function createRepository()
    {
        if (! $this->usesSurrealRepository()) {
            parent::createRepository();

            return;
        }

        $this->schemaManager()->statements([
            sprintf('DEFINE TABLE %s SCHEMAFULL;', $this->normalizedTable()),
            sprintf('DEFINE FIELD migration ON TABLE %s TYPE string;', $this->normalizedTable()),
            sprintf('DEFINE FIELD batch ON TABLE %s TYPE int;', $this->normalizedTable()),
            sprintf('DEFINE INDEX migration_unique ON TABLE %s COLUMNS migration UNIQUE;', $this->normalizedTable()),
        ]);
    }

    public function repositoryExists()
    {
        if (! $this->usesSurrealRepository()) {
            return parent::repositoryExists();
        }

        return $this->schemaManager()->hasTable($this->table);
    }

    public function deleteRepository()
    {
        if (! $this->usesSurrealRepository()) {
            parent::deleteRepository();

            return;
        }

        $this->schemaManager()->statement(sprintf('REMOVE TABLE %s;', $this->normalizedTable()));
    }

    private function usesSurrealRepository(): bool
    {
        $connectionName = $this->connection ?? $this->resolver->getDefaultConnection();

        if ($connectionName === null) {
            return false;
        }

        return $this->connectionDriver($connectionName) === 'surreal';
    }

    private function connectionDriver(string $connectionName): ?string
    {
        return $this->resolver->connection($connectionName)->getConfig('driver');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function selectRows(string $query): array
    {
        $result = Arr::get($this->query($query), '0', []);

        if (! is_array($result)) {
            return [];
        }

        return array_values(array_filter(
            $result,
            static fn (mixed $row): bool => is_array($row),
        ));
    }

    private function selectScalar(string $query, mixed $default = null): mixed
    {
        $result = Arr::get($this->query($query), '0', []);

        if (! is_array($result) || $result === []) {
            return $default;
        }

        return $result[0] ?? $default;
    }

    /**
     * @return list<mixed>
     */
    private function query(string $query): array
    {
        return $this->client->runQuery(
            endpoint: (string) $this->surrealConfig('endpoint'),
            namespace: (string) $this->surrealConfig('namespace'),
            database: (string) $this->surrealConfig('database'),
            username: (string) $this->surrealConfig('username'),
            password: (string) $this->surrealConfig('password'),
            query: $query,
        );
    }

    private function schemaManager(): SurrealSchemaManager
    {
        /** @var SurrealSchemaConnection $connection */
        $connection = $this->resolver->connection($this->connection ?? $this->resolver->getDefaultConnection());

        return $connection->schemaManager();
    }

    private function surrealConfig(string $key): mixed
    {
        return $this->resolver->connection($this->connection ?? $this->resolver->getDefaultConnection())->getConfig($key);
    }

    private function normalizedTable(): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $this->table)) {
            throw new \RuntimeException(sprintf('The Surreal migration table identifier [%s] contains unsupported characters.', $this->table));
        }

        return $this->table;
    }

    private function jsonLiteral(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \RuntimeException('Unable to encode the Surreal migration payload.', previous: $exception);
        }
    }
}
