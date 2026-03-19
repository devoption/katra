<?php

namespace App\Services\Surreal;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

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

    public function runningProcessId(): ?int
    {
        if (! $this->connection->usesLocalRuntime() || ! File::exists($this->connection->runtimePidPath)) {
            return null;
        }

        $pid = (int) trim((string) File::get($this->connection->runtimePidPath));

        if ($pid <= 0) {
            return null;
        }

        return $this->processIsRunning($pid) ? $pid : null;
    }

    private function processIsRunning(int $pid): bool
    {
        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return false;
        }

        try {
            $process = new Process(['ps', '-p', (string) $pid]);
            $process->run();

            return $process->isSuccessful();
        } catch (Throwable) {
            return false;
        }
    }
}
