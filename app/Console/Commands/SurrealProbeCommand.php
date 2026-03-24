<?php

namespace App\Console\Commands;

use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealHttpClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

#[Signature('surreal:probe {--port= : Override the local SurrealDB port} {--path= : Override the local SurrealDB datastore path} {--keep-server : Leave the started SurrealDB process running after the probe}')]
#[Description('Start a local SurrealDB process and prove a Laravel write/read round-trip against it.')]
class SurrealProbeCommand extends Command
{
    public function __construct(
        private readonly SurrealCliClient $cliClient,
        private readonly SurrealHttpClient $httpClient,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->cliClient->isAvailable()) {
            $this->components->error('Unable to find the `surreal` CLI. Install it first or set SURREAL_BINARY to the executable path.');

            return self::FAILURE;
        }

        $host = (string) config('surreal.host');
        $port = (int) ($this->option('port') ?: config('surreal.port'));
        $storagePath = trim((string) ($this->option('path') ?: config('surreal.storage_path')));

        if ($port < 1 || $port > 65535) {
            $this->components->error('The SurrealDB port must be between 1 and 65535.');

            return self::FAILURE;
        }

        if ($storagePath === '') {
            $this->components->error('The SurrealDB storage path must not be empty.');

            return self::FAILURE;
        }

        $bindAddress = sprintf('%s:%d', $host, $port);
        $endpoint = sprintf('ws://%s', $bindAddress);
        $storageEngine = (string) config('surreal.storage_engine');
        $username = (string) config('surreal.username');
        $password = (string) config('surreal.password');
        $namespace = (string) config('surreal.namespace');
        $database = (string) config('surreal.database');
        $recordId = 'probe:'.Str::lower(Str::random(12));

        File::ensureDirectoryExists(dirname($storagePath));

        $server = $this->cliClient->startLocalServer(
            bindAddress: $bindAddress,
            datastorePath: $storagePath,
            username: $username,
            password: $password,
            storageEngine: $storageEngine,
        );

        try {
            if (! $this->httpClient->waitUntilReady($endpoint)) {
                throw new RuntimeException(sprintf('SurrealDB did not become ready on %s.', $endpoint));
            }

            $results = $this->httpClient->runQuery(
                endpoint: $endpoint,
                namespace: $namespace,
                database: $database,
                username: $username,
                password: $password,
                query: sprintf(
                    "CREATE ONLY %s CONTENT { issue: 23, mode: 'local-process', status: 'ok' };\nSELECT * FROM %s;",
                    $recordId,
                    $recordId,
                ),
            );

            $createdRecord = is_array($results[0] ?? null) ? (($results[0] ?? [])[0] ?? null) : null;
            $selectedRecord = is_array($results[1] ?? null) ? (($results[1] ?? [])[0] ?? null) : null;

            if (! is_array($createdRecord) || ! is_array($selectedRecord) || ($selectedRecord['id'] ?? null) !== $recordId) {
                throw new RuntimeException('The SurrealDB probe did not return the expected write/read payload.');
            }

            $this->components->info(sprintf('Started local SurrealDB on %s', $endpoint));
            $this->line(sprintf('Datastore: %s://%s', $storageEngine, $storagePath));
            $this->line(sprintf('Created record: %s', $createdRecord['id']));
            $this->line(sprintf('Read record status: %s', $selectedRecord['status'] ?? 'missing'));
            $this->warn('Embedded verdict: Laravel can drive a local-first SurrealDB child process, but true in-process embedded PHP support remains unproven in this spike.');

            if ($this->option('keep-server')) {
                $this->line('The local SurrealDB process is still running because --keep-server was set.');
            }

            return self::SUCCESS;
        } finally {
            if (! $this->option('keep-server')) {
                $server->stop(1);
            }
        }
    }
}
