<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceChatRequest;
use App\Models\InstanceConnection;
use App\Models\WorkspaceChat;
use App\Support\Chats\WorkspaceChatManager;
use App\Support\Connections\InstanceConnectionManager;
use App\Support\Connections\ViewerIdentityResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function store(
        StoreWorkspaceChatRequest $request,
        InstanceConnectionManager $connectionManager,
        ViewerIdentityResolver $viewerIdentityResolver,
        WorkspaceChatManager $chatManager,
    ): RedirectResponse {
        $submittedToken = (string) $request->validated('chat_submission_token');
        $expectedToken = $request->session()->pull('chat.create_token');

        if (! is_string($expectedToken) || ! hash_equals($expectedToken, $submittedToken)) {
            return to_route('home');
        }

        $activeConnection = $connectionManager->activeConnectionFor(
            $request->user(),
            $request->root(),
            $request->session(),
        );

        if ($activeConnection->kind === InstanceConnection::KIND_SERVER && ! $activeConnection->is_authenticated) {
            return to_route('connections.connect', $activeConnection);
        }

        $workspaces = $connectionManager->workspacesFor($activeConnection);
        $activeWorkspace = $connectionManager->activeWorkspaceFor($activeConnection, $workspaces);
        $viewerIdentity = $viewerIdentityResolver->resolve($request->user(), $activeConnection);

        $chatManager->createChat($activeWorkspace, $request->user(), $viewerIdentity, [
            'name' => $request->validated('chat_name'),
            'kind' => $request->validated('chat_kind'),
            'workspace_agent_id' => $request->integer('workspace_agent_id') ?: null,
        ]);

        return to_route('home');
    }

    public function activate(
        Request $request,
        WorkspaceChat $workspaceChat,
        WorkspaceChatManager $chatManager,
    ): RedirectResponse {
        if ((int) $workspaceChat->workspace->instanceConnection->user_id !== (int) $request->user()->getKey()) {
            abort(404);
        }

        $chatManager->activateChat($workspaceChat);

        return to_route('home');
    }
}
