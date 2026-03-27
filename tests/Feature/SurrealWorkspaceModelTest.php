<?php

use App\Models\SurrealWorkspace;
use App\Models\Workspace;
use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealDocumentStore;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('the surreal workspace model completes a basic crud flow', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/workspace-test-'.Str::uuid());

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingWorkspaceServer($client, $storagePath);
        $port = $server['port'];
        $endpoint = $server['endpoint'];

        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $port);
        config()->set('surreal.endpoint', $endpoint);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'workspace_model_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);

        app()->forgetInstance(SurrealConnection::class);
        app()->forgetInstance(SurrealRuntimeManager::class);
        app()->forgetInstance(SurrealDocumentStore::class);

        $workspace = SurrealWorkspace::create([
            'name' => 'Download Preview Workspace',
            'summary' => 'Proves that Katra can persist through the first Surreal-backed model layer.',
            'status' => 'draft',
        ]);

        expect($workspace->id)->toStartWith('workspace_previews:')
            ->and($workspace->exists)->toBeTrue();

        $fetchedWorkspace = SurrealWorkspace::find($workspace->id);

        expect($fetchedWorkspace)->not->toBeNull()
            ->and($fetchedWorkspace?->name)->toBe('Download Preview Workspace')
            ->and($fetchedWorkspace?->status)->toBe('draft');

        $workspace->status = 'active';
        $workspace->summary = 'Updated through the Surreal-backed save flow.';
        $workspace->save();

        $updatedWorkspace = SurrealWorkspace::find($workspace->id);

        expect($updatedWorkspace)->not->toBeNull()
            ->and($updatedWorkspace?->status)->toBe('active')
            ->and($updatedWorkspace?->summary)->toBe('Updated through the Surreal-backed save flow.');

        expect(SurrealWorkspace::all())->toHaveCount(1);

        $collection = SurrealWorkspace::find([$workspace->id]);

        expect($collection)->toHaveCount(1)
            ->and($collection->first()?->id)->toBe($workspace->id);

        expect($workspace->delete())->toBeTrue()
            ->and(SurrealWorkspace::find($workspace->id))->toBeNull();
    } finally {
        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

test('the desktop preview workspace can be created through the surreal document store', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/workspace-preview-test-'.Str::uuid());

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingWorkspaceServer($client, $storagePath);
        $port = $server['port'];
        $endpoint = $server['endpoint'];

        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $port);
        config()->set('surreal.endpoint', $endpoint);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'workspace_preview_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);

        app()->forgetInstance(SurrealConnection::class);
        app()->forgetInstance(SurrealRuntimeManager::class);
        app()->forgetInstance(SurrealDocumentStore::class);

        $workspace = SurrealWorkspace::desktopPreview();

        expect($workspace->id)->toBe('workspace_previews:desktop-preview')
            ->and($workspace->name)->toBe('Desktop Preview Workspace')
            ->and($workspace->status)->toBe('active');

        $fetchedWorkspace = SurrealWorkspace::find('desktop-preview');

        expect($fetchedWorkspace)->not->toBeNull()
            ->and($fetchedWorkspace?->id)->toBe('workspace_previews:desktop-preview')
            ->and($fetchedWorkspace?->summary)->toContain('Surreal-backed workspace record');
    } finally {
        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

test('the workspace repair migration syncs the surreal sequence after backfill', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/workspace-repair-test-'.Str::uuid());
    $originalDefaultConnection = config('database.default');

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingWorkspaceServer($client, $storagePath);
        $port = $server['port'];
        $endpoint = $server['endpoint'];

        config()->set('database.default', 'surreal');
        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $port);
        config()->set('surreal.endpoint', $endpoint);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'workspace_repair_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);

        DB::purge('surreal');

        $schema = Schema::connection('surreal');
        $connection = DB::connection('surreal');

        $schema->create('connection_workspaces', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('instance_connection_id');
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        $schema->create('workspaces', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('instance_connection_id');
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        $connection->table('workspaces')->insert([
            [
                'id' => 1,
                'instance_connection_id' => 42,
                'name' => 'Alpha',
                'slug' => 'alpha',
                'summary' => 'Legacy alpha workspace.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'instance_connection_id' => 42,
                'name' => 'Beta',
                'slug' => 'beta',
                'summary' => 'Legacy beta workspace.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $migration = require database_path('migrations/2026_03_27_135040_repair_connection_workspaces_table.php');
        $migration->up();

        $workspace = Workspace::create([
            'instance_connection_id' => 42,
            'name' => 'Gamma',
            'slug' => 'gamma',
            'summary' => 'New workspace after repair.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $workspaceIds = collect($connection->table('connection_workspaces')->orderBy('id')->get(['id']))
            ->map(fn (object $workspace): int => (int) ($workspace->id ?? 0))
            ->all();

        expect((int) $workspace->getKey())->toBe(3)
            ->and($workspaceIds)->toBe([1, 2, 3]);
    } finally {
        config()->set('database.default', $originalDefaultConnection);

        DB::purge('surreal');

        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

/**
 * @return array{endpoint: string, port: int, process: Process}
 */
function retryStartingWorkspaceServer(SurrealCliClient $client, string $storagePath, int $attempts = 3): array
{
    $httpClient = app(SurrealHttpClient::class);
    $lastException = null;

    for ($attempt = 1; $attempt <= $attempts; $attempt++) {
        $port = reserveWorkspacePort();
        $endpoint = sprintf('ws://127.0.0.1:%d', $port);
        $process = $client->startLocalServer(
            bindAddress: sprintf('127.0.0.1:%d', $port),
            datastorePath: $storagePath,
            username: 'root',
            password: 'root',
            storageEngine: 'surrealkv',
        );

        if ($httpClient->waitUntilReady($endpoint)) {
            return [
                'endpoint' => $endpoint,
                'port' => $port,
                'process' => $process,
            ];
        }

        $process->stop(1);
        $lastException = new RuntimeException(sprintf('SurrealDB did not become ready on %s.', $endpoint));
    }

    throw $lastException ?? new RuntimeException('Unable to start the SurrealDB workspace test runtime.');
}

function reserveWorkspacePort(): int
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
