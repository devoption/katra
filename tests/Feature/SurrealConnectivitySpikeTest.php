<?php

use App\Services\Surreal\SurrealCliClient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

it('proves a local surrealdb write and read flow from laravel', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/test-'.Str::uuid());

    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', reserveFreePort());
    config()->set('surreal.storage_path', $storagePath);
    config()->set('surreal.namespace', 'katra');
    config()->set('surreal.database', 'spike');

    File::deleteDirectory($storagePath);

    try {
        retryProbeAssertion($this);
    } finally {
        File::deleteDirectory($storagePath);
    }
});

function reserveFreePort(): int
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);

    if ($socket === false) {
        throw new RuntimeException(sprintf('Unable to reserve a free TCP port: %s (%d)', $errorMessage, $errorCode));
    }

    $address = stream_socket_get_name($socket, false);

    fclose($socket);

    if ($address === false) {
        throw new RuntimeException('Unable to determine the reserved TCP port.');
    }

    $port = (int) ltrim((string) strrchr($address, ':'), ':');

    if ($port <= 0) {
        throw new RuntimeException(sprintf('Unable to parse the reserved TCP port from [%s].', $address));
    }

    return $port;
}

function retryProbeAssertion(TestCase $testCase, int $attempts = 3): void
{
    $lastException = null;

    for ($attempt = 1; $attempt <= $attempts; $attempt++) {
        config()->set('surreal.port', reserveFreePort());

        try {
            $testCase->artisan('surreal:probe')
                ->expectsOutputToContain('Started local SurrealDB on')
                ->expectsOutputToContain('Created record:')
                ->expectsOutputToContain('Embedded verdict:')
                ->assertExitCode(0);

            return;
        } catch (Throwable $exception) {
            $lastException = $exception;
        }
    }

    throw $lastException ?? new RuntimeException('The SurrealDB probe did not complete successfully.');
}
