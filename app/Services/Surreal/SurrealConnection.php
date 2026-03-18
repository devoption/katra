<?php

namespace App\Services\Surreal;

class SurrealConnection
{
    public function __construct(
        public readonly string $driver,
        public readonly string $runtime,
        public readonly bool $autostart,
        public readonly string $endpoint,
        public readonly string $host,
        public readonly int $port,
        public readonly string $username,
        public readonly string $password,
        public readonly string $namespace,
        public readonly string $database,
        public readonly string $storageEngine,
        public readonly string $storagePath,
        public readonly string $runtimePidPath,
        public readonly string $runtimeLogPath,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config): self
    {
        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (int) ($config['port'] ?? 18001);

        return new self(
            driver: (string) ($config['driver'] ?? 'cli'),
            runtime: (string) ($config['runtime'] ?? 'local'),
            autostart: (bool) ($config['autostart'] ?? true),
            endpoint: (string) ($config['endpoint'] ?? sprintf('ws://%s:%d', $host, $port)),
            host: $host,
            port: $port,
            username: (string) ($config['username'] ?? 'root'),
            password: (string) ($config['password'] ?? 'root'),
            namespace: (string) ($config['namespace'] ?? 'katra'),
            database: (string) ($config['database'] ?? 'workspace'),
            storageEngine: (string) ($config['storage_engine'] ?? 'surrealkv'),
            storagePath: (string) ($config['storage_path'] ?? storage_path('app/surrealdb/dev')),
            runtimePidPath: (string) ($config['runtime_pid_path'] ?? storage_path('app/surrealdb/runtime.pid')),
            runtimeLogPath: (string) ($config['runtime_log_path'] ?? storage_path('logs/surreal-runtime.log')),
        );
    }

    public function usesLocalRuntime(): bool
    {
        return $this->runtime === 'local';
    }

    public function bindAddress(): string
    {
        return sprintf('%s:%d', $this->host, $this->port);
    }
}
