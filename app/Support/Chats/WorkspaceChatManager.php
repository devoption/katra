<?php

namespace App\Support\Chats;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceAgent;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatMessage;
use App\Models\WorkspaceChatParticipant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkspaceChatManager
{
    public function __construct(
        private WorkspaceAgentManager $workspaceAgentManager,
    ) {}

    /**
     * @return Collection<int, WorkspaceChat>
     */
    public function chatsFor(Workspace $workspace): Collection
    {
        return $workspace->chats()
            ->orderByDesc('updated_at')
            ->orderBy('name')
            ->get()
            ->values();
    }

    /**
     * @param  array{name: string, email: string, initials: string}  $viewerIdentity
     * @param  Collection<int, WorkspaceChat>|null  $chats
     */
    public function activeChatFor(
        Workspace $workspace,
        User $viewer,
        array $viewerIdentity,
        ?Collection $chats = null,
    ): WorkspaceChat {
        $chats ??= $this->chatsFor($workspace);
        $activeChat = $chats->firstWhere('id', $workspace->active_chat_id);

        if (! $activeChat instanceof WorkspaceChat) {
            $activeChat = $chats->first() ?? $this->createChat($workspace, $viewer, $viewerIdentity, [
                'name' => 'General chat',
                'kind' => WorkspaceChat::KIND_GROUP,
            ]);
        }

        if ((int) $workspace->active_chat_id !== (int) $activeChat->getKey()) {
            $workspace->forceFill([
                'active_chat_id' => $activeChat->getKey(),
            ])->save();
        }

        return $activeChat;
    }

    /**
     * @param  array{name: string, email: string, initials: string}  $viewerIdentity
     * @param  array{name: string, kind: string, workspace_agent_id?: int|null}  $attributes
     */
    public function createChat(
        Workspace $workspace,
        User $viewer,
        array $viewerIdentity,
        array $attributes,
    ): WorkspaceChat {
        $chatName = trim($attributes['name']);

        if ($chatName === '') {
            throw ValidationException::withMessages([
                'chat_name' => 'Chat names cannot be blank.',
            ]);
        }

        $chatKind = $attributes['kind'] === WorkspaceChat::KIND_DIRECT
            ? WorkspaceChat::KIND_DIRECT
            : WorkspaceChat::KIND_GROUP;
        $workspaceAgent = $this->workspaceAgentFor($workspace, $attributes['workspace_agent_id'] ?? null);

        $chat = $workspace->chats()->create([
            'name' => $chatName,
            'slug' => $this->nextChatSlug($workspace, $chatName),
            'kind' => $chatKind,
            'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
            'summary' => $this->chatSummary($workspace, $chatName, $chatKind, $workspaceAgent),
            'has_agent_participant' => $workspaceAgent instanceof WorkspaceAgent,
        ]);

        $chat->participants()->create($this->defaultParticipant($viewer, $viewerIdentity));

        if ($workspaceAgent instanceof WorkspaceAgent) {
            $chat->participants()->create($this->agentParticipant($workspaceAgent));
        }

        $workspace->forceFill([
            'active_chat_id' => $chat->getKey(),
        ])->save();

        return $chat;
    }

    public function activateChat(WorkspaceChat $chat): void
    {
        $workspace = $chat->workspace;

        if ((int) $workspace->active_chat_id !== (int) $chat->getKey()) {
            $workspace->forceFill([
                'active_chat_id' => $chat->getKey(),
            ])->save();
        }
    }

    /**
     * @param  array{name: string, email: string, initials: string}  $viewerIdentity
     * @param  array{body: string}  $attributes
     */
    public function createMessage(
        WorkspaceChat $chat,
        User $viewer,
        array $viewerIdentity,
        array $attributes,
    ): WorkspaceChatMessage {
        $message = $chat->messages()->create([
            'sender_type' => WorkspaceChatMessage::SENDER_HUMAN,
            'sender_key' => $this->participantKey($viewer, $viewerIdentity),
            'sender_name' => $viewerIdentity['name'],
            'body' => trim($attributes['body']),
        ]);

        $chat->touch();
        $this->activateChat($chat);

        return $message;
    }

    /**
     * @param  array{name: string, email: string, initials: string}  $viewerIdentity
     * @return array<string, int|string|null>
     */
    private function defaultParticipant(User $viewer, array $viewerIdentity): array
    {
        return [
            'user_id' => $viewer->getKey(),
            'workspace_agent_id' => null,
            'participant_type' => WorkspaceChatParticipant::TYPE_HUMAN,
            'participant_key' => $this->participantKey($viewer, $viewerIdentity),
            'display_name' => $viewerIdentity['name'],
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    private function agentParticipant(WorkspaceAgent $workspaceAgent): array
    {
        return [
            'user_id' => null,
            'workspace_agent_id' => $workspaceAgent->getKey(),
            'participant_type' => WorkspaceChatParticipant::TYPE_AGENT,
            'participant_key' => 'agent:'.$workspaceAgent->agent_key,
            'display_name' => $workspaceAgent->name,
        ];
    }

    /**
     * @param  array{name: string, email: string, initials: string}  $viewerIdentity
     */
    private function participantKey(User $viewer, array $viewerIdentity): string
    {
        $email = trim((string) ($viewerIdentity['email'] ?? ''));

        if ($email !== '') {
            return 'human:'.Str::lower($email);
        }

        return 'human:user-'.$viewer->getKey();
    }

    private function nextChatSlug(Workspace $workspace, string $chatName): string
    {
        $baseSlug = Str::slug($chatName);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'chat';
        $slug = $baseSlug;
        $suffix = 2;

        while ($workspace->chats()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function chatSummary(
        Workspace $workspace,
        string $chatName,
        string $chatKind,
        ?WorkspaceAgent $workspaceAgent = null,
    ): string {
        if ($chatKind === WorkspaceChat::KIND_DIRECT) {
            if ($workspaceAgent instanceof WorkspaceAgent) {
                return sprintf(
                    '%s is a private direct chat with %s inside the %s workspace.',
                    $chatName,
                    $workspaceAgent->name,
                    $workspace->name,
                );
            }

            return sprintf(
                '%s is a private direct chat inside the %s workspace.',
                $chatName,
                $workspace->name,
            );
        }

        if ($workspaceAgent instanceof WorkspaceAgent) {
            return sprintf(
                '%s is a private group chat with %s inside the %s workspace.',
                $chatName,
                $workspaceAgent->name,
                $workspace->name,
            );
        }

        return sprintf(
            '%s is a private group chat inside the %s workspace.',
            $chatName,
            $workspace->name,
        );
    }

    private function workspaceAgentFor(Workspace $workspace, ?int $workspaceAgentId): ?WorkspaceAgent
    {
        $workspaceAgent = $this->workspaceAgentManager->agentForWorkspace($workspace, $workspaceAgentId);

        if ($workspaceAgentId !== null && ! $workspaceAgent instanceof WorkspaceAgent) {
            throw ValidationException::withMessages([
                'workspace_agent_id' => 'Choose a valid agent for this workspace.',
            ]);
        }

        return $workspaceAgent;
    }
}
