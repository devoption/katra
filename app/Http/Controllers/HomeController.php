<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class HomeController extends Controller
{
    public function __invoke(SurrealConnection $connection, SurrealCliClient $client, SurrealRuntimeManager $runtimeManager): View
    {
        $workspace = null;
        $surrealStatus = 'degraded';
        $surrealMessage = 'The Surreal-backed model layer is wired in, but the runtime is not available yet on this machine.';
        $runtimeReady = false;
        $runtimeLabel = $this->runtimeLabel($connection, $client);
        $surrealDetails = [
            ['label' => 'Runtime', 'value' => 'Unavailable'],
            ['label' => 'Binary', 'value' => $this->binaryLabel($connection, $client)],
            ['label' => 'Endpoint', 'value' => $connection->endpoint],
        ];

        try {
            $runtimeReady = $runtimeManager->ensureReady();
            $runningProcessId = $runtimeManager->runningProcessId();

            $surrealDetails[0]['value'] = $runtimeReady ? 'Running' : 'Unavailable';

            if ($runningProcessId !== null) {
                $surrealDetails[] = ['label' => 'Process', 'value' => sprintf('PID %d', $runningProcessId)];
            }

            if (! $runtimeReady) {
                $surrealMessage = $connection->usesLocalRuntime()
                    ? sprintf('The %s is configured for %s, but it is not responding yet.', $runtimeLabel, $connection->endpoint)
                    : sprintf('The remote Surreal runtime is not responding at %s.', $connection->endpoint);

                return view('welcome', [
                    'workspace' => $workspace,
                    'surrealStatus' => $surrealStatus,
                    'surrealMessage' => $surrealMessage,
                    'surrealDetails' => $surrealDetails,
                ]);
            }

            $workspace = Workspace::desktopPreview();
            $surrealStatus = 'connected';
            $surrealMessage = sprintf('The preview workspace is persisted through the %s at %s.', $runtimeLabel, $connection->endpoint);
        } catch (Throwable $exception) {
            if ($exception instanceof RuntimeException) {
                $surrealStatus = $runtimeReady ? 'runtime-ready' : 'degraded';
                $surrealMessage = $runtimeReady
                    ? sprintf('The %s is running at %s, but the preview workspace bootstrap failed: %s', $runtimeLabel, $connection->endpoint, Str::limit($exception->getMessage(), 160))
                    : Str::limit($exception->getMessage(), 220);
            } else {
                report($exception);
                $surrealMessage = 'The Surreal-backed model layer hit an unexpected error while loading the preview workspace.';
            }
        }

        return view('welcome', [
            'workspace' => $workspace,
            'surrealStatus' => $surrealStatus,
            'surrealMessage' => $surrealMessage,
            'surrealDetails' => $surrealDetails,
        ]);
    }

    private function runtimeLabel(SurrealConnection $connection, SurrealCliClient $client): string
    {
        return match (true) {
            ! $connection->usesLocalRuntime() => 'remote Surreal runtime',
            $client->usesBundledBinary() => 'bundled Surreal runtime',
            default => 'local Surreal runtime',
        };
    }

    private function binaryLabel(SurrealConnection $connection, SurrealCliClient $client): string
    {
        return match (true) {
            ! $connection->usesLocalRuntime() => 'Remote endpoint',
            $client->usesBundledBinary() => 'Bundled preview binary',
            $client->isAvailable() => 'Machine-local CLI',
            default => 'Missing',
        };
    }
}
