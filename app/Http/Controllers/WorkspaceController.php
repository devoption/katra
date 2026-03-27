<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Models\InstanceConnection;
use App\Models\Workspace;
use App\Support\Connections\InstanceConnectionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function store(
        StoreWorkspaceRequest $request,
        InstanceConnectionManager $connectionManager,
    ): RedirectResponse {
        $activeConnection = $connectionManager->activeConnectionFor(
            $request->user(),
            $request->root(),
            $request->session(),
        );

        if ($activeConnection->kind === InstanceConnection::KIND_SERVER && ! $activeConnection->is_authenticated) {
            return to_route('connections.connect', $activeConnection);
        }

        $connectionManager->createWorkspace($activeConnection, [
            'name' => $request->validated('workspace_name'),
        ]);

        return to_route('home');
    }

    public function activate(
        Request $request,
        Workspace $workspace,
        InstanceConnectionManager $connectionManager,
    ): RedirectResponse {
        if ((int) $workspace->instanceConnection->user_id !== (int) $request->user()->getKey()) {
            abort(404);
        }

        $connectionManager->activateWorkspace($workspace, $request->session());

        if (
            $workspace->instanceConnection->kind === InstanceConnection::KIND_SERVER
            && ! $workspace->instanceConnection->is_authenticated
        ) {
            return to_route('connections.connect', $workspace->instanceConnection);
        }

        return to_route('home');
    }
}
