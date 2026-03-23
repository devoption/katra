<?php

namespace App\Services\Surreal\Schema;

use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Closure;
use Generator;
use Illuminate\Database\Connection;
use Illuminate\Http\Client\Factory;
use RuntimeException;

class SurrealSchemaConnection extends Connection
{
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

    public function statement($query, $bindings = []): bool
    {
        return $this->manager->statement((string) $query);
    }

    public function getDriverName(): string
    {
        return 'surreal';
    }

    public function select($query, $bindings = [], $useReadPdo = true, array $fetchUsing = []): array
    {
        throw $this->unsupportedOperation('select queries');
    }

    public function cursor($query, $bindings = [], $useReadPdo = true, array $fetchUsing = []): Generator
    {
        throw $this->unsupportedOperation('cursors');
    }

    public function insert($query, $bindings = []): bool
    {
        throw $this->unsupportedOperation('insert queries');
    }

    public function update($query, $bindings = []): int
    {
        throw $this->unsupportedOperation('update queries');
    }

    public function delete($query, $bindings = []): int
    {
        throw $this->unsupportedOperation('delete queries');
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

    protected function getDefaultSchemaGrammar(): SurrealSchemaGrammar
    {
        return new SurrealSchemaGrammar($this);
    }

    private function unsupportedOperation(string $operation): RuntimeException
    {
        return new RuntimeException(sprintf(
            'SurrealSchemaConnection does not support %s. Use the Surreal document layer for data access.',
            $operation,
        ));
    }
}
