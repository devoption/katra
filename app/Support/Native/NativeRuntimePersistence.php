<?php

namespace App\Support\Native;

use Illuminate\Contracts\Config\Repository;

class NativeRuntimePersistence
{
    public function __construct(private Repository $config) {}

    public function configure(): void
    {
        if (! $this->isRunningInNativeRuntime()) {
            return;
        }

        $databaseConnection = (string) $this->config->get('nativephp.persistence.database_connection', 'surreal');
        $sessionDriver = (string) $this->config->get('nativephp.persistence.session_driver', 'surreal');
        $cacheStore = (string) $this->config->get('nativephp.persistence.cache_store', 'surreal');
        $queueConnection = (string) $this->config->get('nativephp.persistence.queue_connection', 'surreal');
        $limiterStore = (string) $this->config->get('nativephp.persistence.limiter_store', $this->config->get('cache.limiter', 'file'));

        $updates = [
            'database.default' => $databaseConnection,
            'database.migrations.connection' => $databaseConnection,
            'session.driver' => $sessionDriver,
            'cache.default' => $cacheStore,
            'cache.limiter' => $limiterStore,
            'cache.stores.database.connection' => $databaseConnection,
            'cache.stores.database.lock_connection' => $databaseConnection,
            'cache.stores.surreal.connection' => $databaseConnection,
            'cache.stores.surreal.lock_connection' => $databaseConnection,
            'queue.default' => $queueConnection,
            'queue.failed.database' => $databaseConnection,
            'queue.batching.database' => $databaseConnection,
            'queue.connections.database.connection' => $databaseConnection,
            'queue.connections.surreal.connection' => $databaseConnection,
        ];

        if (in_array($this->config->get('session.connection'), [null, '', 'nativephp', 'sqlite'], true)) {
            $updates['session.connection'] = $databaseConnection;
        }

        if ($this->config->get('ai.caching.embeddings.store') === 'database') {
            $updates['ai.caching.embeddings.store'] = $cacheStore;
        }

        $this->config->set($updates);
    }

    private function isRunningInNativeRuntime(): bool
    {
        return (bool) $this->config->get('nativephp-internal.running', false);
    }
}
