<?php

namespace App\Services\Surreal\Schema;

use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Database\Connection;
use Illuminate\Http\Client\Factory;
use PDO;

class SurrealSchemaConnection extends Connection
{
    private ?SurrealSchemaBuilder $schemaBuilder = null;

    public function __construct(
        array $config,
        private readonly SurrealSchemaManager $manager,
        string $name,
    ) {
        $config['name'] = $name;

        parent::__construct(
            new PDO('sqlite::memory:'),
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

        return tap(new self(
            $config,
            new SurrealSchemaManager(
                $httpClient,
                $surrealConnection,
                new SurrealRuntimeManager($client, $httpClient, $surrealConnection),
            ),
            $name,
        ), function (self $connection): void {
            $connection->useDefaultSchemaGrammar();
        });
    }

    public function statement($query, $bindings = []): bool
    {
        return $this->manager->statement((string) $query);
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

    protected function getDefaultSchemaGrammar(): SurrealSchemaGrammar
    {
        return new SurrealSchemaGrammar($this);
    }
}
