<?php

namespace App\Support\Chats;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatMessage;
use App\Models\WorkspaceChatParticipant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class WorkspaceChatManager
{
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
     * @param  array{name: string, kind: string}  $attributes
     */
    public function createChat(
        Workspace $workspace,
        User $viewer,
        array $viewerIdentity,
        array $attributes,
    ): WorkspaceChat {
        $chatName = trim($attributes['name']);
        $chatKind = $attributes['kind'] === WorkspaceChat::KIND_DIRECT
            ? WorkspaceChat::KIND_DIRECT
            : WorkspaceChat::KIND_GROUP;

        $chat = $workspace->chats()->create([
            'name' => $chatName,
            'slug' => $this->nextChatSlug($workspace, $chatName),
            'kind' => $chatKind,
            'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
            'summary' => $this->chatSummary($workspace, $chatName, $chatKind),
        ]);

        $chat->participants()->create($this->defaultParticipant($viewer, $viewerIdentity));

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
            'participant_type' => WorkspaceChatParticipant::TYPE_HUMAN,
            'participant_key' => $this->participantKey($viewer, $viewerIdentity),
            'display_name' => $viewerIdentity['name'],
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

    private function chatSummary(Workspace $workspace, string $chatName, string $chatKind): string
    {
        if ($chatKind === WorkspaceChat::KIND_DIRECT) {
            return sprintf(
                '%s is a private direct chat inside the %s workspace.',
                $chatName,
                $workspace->name,
            );
        }

        return sprintf(
            '%s is a private group chat inside the %s workspace.',
            $chatName,
            $workspace->name,
        );
    }
}
