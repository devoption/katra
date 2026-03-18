<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\Surreal\SurrealConnection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class HomeController extends Controller
{
    public function __invoke(SurrealConnection $connection): View
    {
        $workspace = null;
        $surrealStatus = 'degraded';
        $surrealMessage = 'The Surreal-backed model layer is wired in, but the runtime is not available yet on this machine.';

        try {
            $workspace = Workspace::desktopPreview();
            $surrealStatus = 'connected';
            $surrealMessage = sprintf('The preview workspace is persisted through the Surreal foundation at %s.', $connection->endpoint);
        } catch (Throwable $exception) {
            if ($exception instanceof RuntimeException) {
                $surrealMessage = Str::limit($exception->getMessage(), 220);
            } else {
                report($exception);
                $surrealMessage = 'The Surreal-backed model layer hit an unexpected error while loading the preview workspace.';
            }
        }

        return view('welcome', [
            'workspace' => $workspace,
            'surrealStatus' => $surrealStatus,
            'surrealMessage' => $surrealMessage,
        ]);
    }
}
