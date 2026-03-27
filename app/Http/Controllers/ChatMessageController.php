<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceChatMessageRequest;
use App\Models\WorkspaceChat;
use App\Support\Chats\WorkspaceChatManager;
use App\Support\Connections\ViewerIdentityResolver;
use Illuminate\Http\RedirectResponse;

class ChatMessageController extends Controller
{
    public function store(
        StoreWorkspaceChatMessageRequest $request,
        WorkspaceChat $workspaceChat,
        ViewerIdentityResolver $viewerIdentityResolver,
        WorkspaceChatManager $chatManager,
    ): RedirectResponse {
        if ((int) $workspaceChat->workspace->instanceConnection->user_id !== (int) $request->user()->getKey()) {
            abort(404);
        }

        $viewerIdentity = $viewerIdentityResolver->resolve($request->user(), $workspaceChat->workspace->instanceConnection);

        $chatManager->createMessage($workspaceChat, $request->user(), $viewerIdentity, [
            'body' => $request->validated('message_body'),
        ]);

        return to_route('home');
    }
}
