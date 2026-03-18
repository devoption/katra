<?php

namespace App\Services\Surreal;

use Illuminate\Support\Facades\File;
use RuntimeException;

class SurrealRuntimeManager
{
    public function __construct(
        private readonly SurrealCliClient $client,
        private readonly SurrealConnection $connection,
    ) {}

    public function ensureReady(): bool
    {
        if (! $this->client->isAvailable()) {
            return false;
        }

        if ($this->client->isReady($this->connection->endpoint)) {
            return true;
        }

        if (! $this->connection->usesLocalRuntime() || ! $this->connection->autostart) {
            return false;
        }

        File::ensureDirectoryExists(dirname($this->connection->storagePath));
        File::ensureDirectoryExists(dirname($this->connection->runtimePidPath));
        File::ensureDirectoryExists(dirname($this->connection->runtimeLogPath));

        $lockPath = $this->connection->runtimePidPath.'.lock';
        $lockHandle = fopen($lockPath, 'c+');

        if ($lockHandle === false) {
            throw new RuntimeException(sprintf('Unable to open the SurrealDB runtime lock file at [%s].', $lockPath));
        }

        try {
            if (! flock($lockHandle, LOCK_EX)) {
                throw new RuntimeException(sprintf('Unable to lock the SurrealDB runtime lock file at [%s].', $lockPath));
            }

            if ($this->client->isReady($this->connection->endpoint)) {
                return true;
            }

            $pid = $this->client->startDetachedLocalServer(
                bindAddress: $this->connection->bindAddress(),
                datastorePath: $this->connection->storagePath,
                username: $this->connection->username,
                password: $this->connection->password,
                storageEngine: $this->connection->storageEngine,
                logPath: $this->connection->runtimeLogPath,
            );

            File::put($this->connection->runtimePidPath, (string) $pid);

            return $this->client->waitUntilReady($this->connection->endpoint, attempts: 30, sleepMilliseconds: 250);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }
}
