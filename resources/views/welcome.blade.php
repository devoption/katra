<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Katra') }} Desktop Shell</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-mono:400,500|space-grotesk:400,500,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="shell-app h-screen overflow-hidden antialiased">
        <main class="h-screen w-screen">
            @if (! $mvpShellEnabled)
                <section class="flex h-full items-center justify-center">
                    <x-desktop.panel eyebrow="Desktop shell rollout" title="The MVP workspace shell is currently hidden." subtitle="Pennant is keeping the new shell staged off while the next round of design feedback settles." class="w-full max-w-3xl bg-[#3B4252]">
                        <div class="flex flex-wrap gap-2">
                            <x-desktop.status-pill label="mvp shell disabled" tone="amber" />
                            <x-desktop.status-pill label="safe fallback" />
                        </div>
                    </x-desktop.panel>
                </section>
            @else
                @php
                    $searchResults = [
                        [
                            'label' => 'Workspaces',
                            'items' => [
                                ['title' => $activeWorkspace['label'], 'meta' => 'Workspace', 'summary' => 'Current active workspace on '.$activeConnection->name.'.'],
                                ['title' => 'General', 'meta' => 'Workspace', 'summary' => 'Default workspace for the current connection.'],
                            ],
                        ],
                        [
                            'label' => 'People and agents',
                            'items' => [
                                ['title' => $viewerName, 'meta' => 'Human', 'summary' => 'Direct conversation and workspace owner context.'],
                                ['title' => 'Planner Agent', 'meta' => 'Worker', 'summary' => 'Planning and structuring support for the active room.'],
                            ],
                        ],
                        [
                            'label' => 'Nodes',
                            'items' => [
                                ['title' => 'Shape the MVP shell', 'meta' => 'Task', 'summary' => 'Open node linked into the current conversation.'],
                                ['title' => 'Sidebar studies', 'meta' => 'Artifact', 'summary' => 'Mock artifact connected to the design room.'],
                            ],
                        ],
                    ];

                    $conversationSeedMessages = collect($messages)
                        ->values()
                        ->map(fn (array $message, int $index): array => [
                            'id' => 'seed-'.$index,
                            'sender' => $message['speaker'],
                            'role' => $message['role'],
                            'meta' => $message['meta'],
                            'body' => $message['body'],
                            'direction' => $message['speaker'] === 'You' ? 'outgoing' : 'incoming',
                            'attachments' => [],
                        ])
                        ->all();

                    $conversationResponders = collect($participants)
                        ->filter(fn (array $participant): bool => $participant['meta'] !== 'Human')
                        ->values()
                        ->map(fn (array $participant): array => [
                            'label' => $participant['label'],
                            'role' => $participant['meta'],
                        ])
                        ->all();

                    $conversationMockReplies = [
                        'I can break this into a couple of linked nodes without changing the flow of the room.',
                        'That looks good. I would tighten the interaction first, then let the linked work follow behind it.',
                        'We can keep the room focused and still attach the next task, artifact, or decision from the context rail.',
                        'This conversation feels clearer when the structure stays quiet and the actions stay close to the composer.',
                        'I can take the next pass on this and keep the changes scoped to the active conversation.',
                    ];
                @endphp
                <div data-desktop-shell class="relative h-full overflow-hidden">
                    <div data-shell-grid class="grid h-full xl:grid-cols-[320px_minmax(0,1fr)]">
                    <aside data-sidebar class="shell-panel flex min-h-0 flex-col overflow-hidden px-4 py-3 transition-[opacity,transform] duration-200">
                        <div class="flex items-center justify-between gap-3 py-1">
                            <div class="py-2">
                                <img src="{{ asset('katra-logo.svg') }}" alt="Katra" class="shell-logo-dark h-7 w-auto" />
                                <img src="{{ asset('katra-logo-light.svg') }}" alt="Katra" class="shell-logo-light h-7 w-auto" />
                            </div>
                            <button
                                type="button"
                                data-sidebar-toggle
                                class="shell-icon-button inline-flex h-7 w-7 items-center justify-center rounded-full transition-colors"
                                aria-label="Collapse sidebar"
                                title="Collapse sidebar"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M12.5 5 7.5 10l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 min-h-0 flex-1 overflow-y-auto">
                            <div class="space-y-4 pr-1">
                                <x-desktop.nav-section label="Workspaces" collapsible open action-label="Create workspace" action-dialog-id="workspace-creator-modal">
                                    @foreach ($workspaceLinks as $item)
                                        <x-desktop.nav-item
                                            :label="$item['label']"
                                            :prefix="$item['prefix']"
                                            :tone="$item['tone']"
                                            :active="$item['active']"
                                            :action="$item['active'] ? null : route('workspaces.activate', $item['id'])"
                                        />
                                    @endforeach
                                </x-desktop.nav-section>

                                @if ($favoritesEnabled)
                                    <x-desktop.nav-section label="Favorites" collapsible open>
                                        @foreach ($favoriteLinks as $item)
                                            <x-desktop.nav-item :label="$item['label']" :prefix="$item['prefix']" :tone="$item['tone']" :active="$item['active'] ?? false" :muted="$item['muted'] ?? false" />
                                        @endforeach
                                    </x-desktop.nav-section>
                                @endif

                                <x-desktop.nav-section label="Rooms" collapsible open action-label="Create room" action-dialog-id="room-creator-modal">
                                    @foreach ($roomLinks as $item)
                                        <x-desktop.nav-item :label="$item['label']" :prefix="$item['prefix']" :tone="$item['tone']" :active="$item['active'] ?? false" :muted="$item['muted'] ?? false" />
                                    @endforeach
                                </x-desktop.nav-section>

                                <x-desktop.nav-section label="Chats" collapsible open action-label="Create chat" action-dialog-id="chat-creator-modal">
                                    @foreach ($chatLinks as $item)
                                        <x-desktop.nav-item :label="$item['label']" :prefix="$item['prefix']" :tone="$item['tone']" :active="$item['active'] ?? false" :muted="$item['muted'] ?? false" />
                                    @endforeach
                                </x-desktop.nav-section>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button
                                type="button"
                                data-dialog-target="connection-creator-modal"
                                class="shell-surface shell-connection-trigger flex w-full items-center gap-3 rounded-[20px] px-3 py-3 text-left"
                            >
                                <span class="shell-accent-chip flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-semibold uppercase tracking-[0.02em]">
                                    {{ $activeConnection->is_current_instance ? 'K' : strtoupper(substr($activeConnection->name, 0, 1)) }}
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.12em]">Connections</span>
                                    <span class="shell-text mt-1 block truncate text-sm font-semibold">{{ $activeConnection->name }}</span>
                                </span>

                                <span class="shell-text-info-strong shrink-0 font-mono text-[10px] uppercase tracking-[0.12em]">Current</span>
                            </button>
                        </div>

                        <x-desktop.profile-menu :name="$viewerName" :email="$viewerEmail" :initials="$viewerInitials" />
                    </aside>

                    <section class="shell-panel flex min-h-0 flex-col">
                        <header class="flex flex-col gap-4 px-4 py-4">
                            <div class="relative z-30 flex items-center gap-3">
                                <button
                                    type="button"
                                    data-sidebar-expand
                                    class="shell-icon-button mt-0.5 hidden h-8 w-8 shrink-0 items-center justify-center rounded-full transition-colors"
                                    aria-label="Expand sidebar"
                                    title="Expand sidebar"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M7.5 5 12.5 10l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>

                                <div class="relative min-w-0 flex-1">
                                    <div class="shell-surface flex h-10 min-w-0 items-center gap-3 rounded-full px-4">
                                        <x-mdi-magnify class="h-4 w-4 shrink-0 shell-text-faint" />
                                        <input
                                            type="text"
                                            data-search-input
                                            class="shell-text min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]"
                                            placeholder="Search conversations, people, and nodes"
                                            aria-label="Search conversations, people, and nodes"
                                        />
                                    </div>

                                    <div
                                        data-search-overlay
                                        class="shell-panel shell-shadow hidden absolute top-full right-0 left-0 z-20 mt-3 max-h-[70vh] overflow-y-auto rounded-[24px] p-4"
                                    >
                                        <div class="space-y-4">
                                            @foreach ($searchResults as $group)
                                                <div class="space-y-2">
                                                    <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">{{ $group['label'] }}</p>
                                                    <div class="space-y-2">
                                                        @foreach ($group['items'] as $item)
                                                            <button
                                                                type="button"
                                                                class="shell-elevated flex w-full items-start justify-between gap-4 rounded-[20px] px-4 py-3 text-left transition-colors hover:bg-[var(--shell-surface)]"
                                                            >
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="shell-text text-sm font-medium">{{ $item['title'] }}</p>
                                                                    <p class="shell-text-soft mt-1 text-sm leading-6">{{ $item['summary'] }}</p>
                                                                </div>
                                                                <p class="shell-text-info-strong shrink-0 font-mono text-[10px] uppercase tracking-[0.12em]">{{ $item['meta'] }}</p>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    data-right-rail-toggle
                                    class="shell-icon-button inline-flex h-8 w-8 items-center justify-center rounded-full transition-colors"
                                    aria-label="Open context panel"
                                    title="Open context panel"
                                    aria-expanded="false"
                                >
                                    <svg data-right-rail-toggle-open-icon class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M12.5 5 7.5 10l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <svg data-right-rail-toggle-close-icon class="hidden h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M7.5 5 12.5 10l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <div>
                                <p class="shell-text-info font-mono text-[10px] uppercase tracking-[0.12em]">Workspace</p>
                                <h1 class="shell-text mt-1.5 text-2xl font-semibold tracking-[-0.03em]">{{ $activeWorkspace['label'] }}</h1>
                                <p class="shell-text-soft mt-2 max-w-2xl text-sm leading-6">
                                    {{ $activeWorkspace['summary'] }}
                                </p>
                            </div>
                            </div>
                        </header>

                        <div class="min-h-0 flex-1 px-4 pb-4">
                            <div class="flex h-full min-h-0 flex-col">
                                <div
                                    data-conversation-stream
                                    class="min-h-0 flex-1 space-y-4 overflow-y-auto px-1 pb-4"
                                >
                                    @foreach ($conversationSeedMessages as $message)
                                        @php
                                            $isOutgoing = $message['direction'] === 'outgoing';
                                            $messageRoleTone = match ($message['role']) {
                                                'Human' => 'shell-text-faint',
                                                'Agent' => 'text-[color:var(--shell-accent)]',
                                                default => 'shell-text-info-strong',
                                            };
                                            $messageBubbleTone = $isOutgoing ? 'shell-accent-soft' : 'shell-elevated';
                                        @endphp

                                        <article class="flex {{ $isOutgoing ? 'justify-end' : 'justify-start' }}">
                                            <div class="flex max-w-[78%] flex-col gap-2 {{ $isOutgoing ? 'items-end' : 'items-start' }}">
                                                <div class="flex items-center gap-2 px-1">
                                                    <span class="shell-text text-sm font-semibold">{{ $message['sender'] }}</span>
                                                    <span class="font-mono text-[10px] uppercase tracking-[0.12em] {{ $messageRoleTone }}">{{ $message['role'] }}</span>
                                                    <span class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">{{ $message['meta'] }}</span>
                                                </div>

                                                <div class="{{ $messageBubbleTone }} w-full rounded-[26px] px-4 py-3">
                                                    <p class="shell-text text-[15px] leading-7 whitespace-pre-wrap">{{ $message['body'] }}</p>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>

                                <div class="shell-surface mt-2 rounded-[26px] px-4 py-3">
                                    <div data-message-attachments class="hidden flex-wrap gap-2 pb-3"></div>

                                    <div
                                        data-voice-indicator
                                        class="shell-accent-soft shell-text hidden items-center gap-2 rounded-full px-3 py-2 text-sm font-medium"
                                    >
                                        <x-mdi-microphone class="h-4 w-4" />
                                        <span>Voice mode selected</span>
                                    </div>

                                    <textarea
                                        data-message-input
                                        rows="1"
                                        class="shell-text min-h-[56px] w-full resize-none bg-transparent pt-1 text-[15px] leading-7 outline-none placeholder:text-[color:var(--shell-text-faint)]"
                                        placeholder="Message {{ $activeWorkspace['label'] }}"
                                        aria-label="Message {{ $activeWorkspace['label'] }}"
                                    ></textarea>

                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="file"
                                                data-message-file-input
                                                class="hidden"
                                                multiple
                                                aria-hidden="true"
                                            />
                                            <button
                                                type="button"
                                                data-attach-file
                                                class="shell-elevated shell-hover-surface inline-flex h-10 w-10 items-center justify-center rounded-full transition-colors"
                                                aria-label="Attach file"
                                                title="Attach file"
                                            >
                                                <x-mdi-paperclip class="h-4 w-4" />
                                            </button>
                                            <button
                                                type="button"
                                                data-voice-toggle
                                                class="shell-elevated shell-hover-surface inline-flex h-10 w-10 items-center justify-center rounded-full transition-colors"
                                                aria-label="Toggle voice mode"
                                                title="Toggle voice mode"
                                                aria-pressed="false"
                                            >
                                                <x-mdi-microphone class="h-4 w-4" />
                                            </button>
                                        </div>

                                        <button
                                            type="button"
                                            data-send-message
                                            class="shell-accent-chip inline-flex h-10 w-10 items-center justify-center rounded-full transition-colors hover:bg-[var(--shell-accent-hover)]"
                                            aria-label="Send message"
                                            title="Send message"
                                        >
                                            <x-mdi-send class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside
                        data-right-rail
                        class="shell-context-panel absolute inset-y-0 right-0 z-30 min-h-0 max-w-[calc(100vw-32px)] overflow-hidden px-3 py-4 transition-[opacity,transform] duration-200"
                    >
                        <button
                            type="button"
                            data-right-rail-resize-handle
                            class="absolute inset-y-0 left-0 z-10 w-3 cursor-col-resize"
                            aria-label="Resize context panel"
                            title="Resize context panel"
                        >
                            <span class="pointer-events-none absolute top-1/2 left-0 flex h-14 w-4 -translate-y-1/2 items-center justify-center rounded-r-full bg-[color:var(--shell-accent)]">
                                <x-mdi-dots-vertical class="h-5 w-5 text-[color:var(--shell-accent-text)] opacity-80" />
                            </span>
                        </button>

                        <div class="flex h-full min-h-0 flex-col pl-3">
                        <div class="flex items-center justify-between gap-3 pb-3">
                            <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">Context</p>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    data-right-rail-pin
                                    class="shell-icon-button inline-flex h-7 w-7 items-center justify-center rounded-full transition-colors"
                                    aria-label="Pin context panel"
                                    title="Pin context panel"
                                    aria-pressed="false"
                                >
                                    <x-mdi-pin class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    type="button"
                                    data-right-rail-close
                                    class="shell-icon-button inline-flex h-7 w-7 items-center justify-center rounded-full transition-colors"
                                    aria-label="Close context panel"
                                    title="Close context panel"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M7.5 5 12.5 10l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="min-h-0 overflow-y-auto pr-1">
                            <section class="shell-surface rounded-[22px] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">Participants</p>
                                        <p class="shell-text-soft mt-2 text-sm">{{ count($participants) }} in this conversation</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="shell-icon-button inline-flex h-8 items-center justify-center rounded-full px-3 text-sm font-medium transition-colors"
                                    >
                                        Manage people
                                    </button>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($participants as $participant)
                                        @php
                                            $participantTone = match ($participant['meta']) {
                                                'Human' => 'shell-human-chip',
                                                'Worker', 'Model' => 'shell-bot-chip',
                                                default => 'shell-room-chip',
                                            };

                                            $participantPrefix = match ($participant['meta']) {
                                                'Human' => str($participant['label'])->substr(0, 1)->upper()->value(),
                                                default => '@',
                                            };
                                        @endphp

                                        <div class="shell-elevated inline-flex items-center gap-2 rounded-full px-2.5 py-2">
                                            <span class="{{ $participantTone }} flex h-7 w-7 items-center justify-center rounded-full font-mono text-xs uppercase">
                                                {{ $participantPrefix }}
                                            </span>
                                            <span class="shell-text text-sm font-medium">{{ $participant['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <section class="shell-surface mt-3 rounded-[22px] p-4" data-node-tabs>
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">Nodes</p>
                                        <p class="shell-text-soft mt-2 text-sm">Linked work for this conversation.</p>
                                    </div>
                                </div>

                                <div class="shell-elevated mt-4 inline-flex rounded-full p-1">
                                    @foreach ($conversationNodeTabs as $index => $tab)
                                        <button
                                            type="button"
                                            data-node-tab-button
                                            data-node-tab-target="{{ $tab['key'] }}"
                                            @class([
                                                'shell-theme-option rounded-full px-4 py-2 text-sm font-medium transition-colors',
                                                'shell-accent-chip' => $index === 0,
                                            ])
                                            aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                                        >
                                            {{ $tab['label'] }}
                                        </button>
                                    @endforeach
                                </div>

                                <div class="mt-4 space-y-4">
                                    @php
                                        $assignableAgents = collect($participants)
                                            ->filter(fn (array $participant): bool => $participant['meta'] === 'Worker')
                                            ->values();
                                    @endphp

                                    @foreach ($conversationNodeTabs as $index => $tab)
                                        <div
                                            data-node-tab-panel="{{ $tab['key'] }}"
                                            @class([
                                                'space-y-4',
                                                'hidden' => $index !== 0,
                                            ])
                                        >
                                            @foreach ($tab['groups'] as $group)
                                                <div class="space-y-2">
                                                    <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">{{ $group['label'] }}</p>

                                                    @if (count($group['items']) === 0)
                                                        <div class="shell-elevated rounded-[18px] px-3 py-3">
                                                            <p class="shell-text-subtle text-sm">No {{ strtolower($group['label']) }} here yet.</p>
                                                        </div>
                                                    @else
                                                        <div class="space-y-2">
                                                            @foreach ($group['items'] as $itemIndex => $item)
                                                                <div data-node-item class="space-y-2">
                                                                    <button
                                                                        type="button"
                                                                        data-node-item-button
                                                                        class="shell-elevated flex w-full items-start justify-between gap-3 rounded-[18px] px-3 py-3 text-left transition-colors hover:bg-[var(--shell-panel)]"
                                                                        aria-expanded="{{ $itemIndex === 0 ? 'true' : 'false' }}"
                                                                    >
                                                                        <div class="min-w-0 flex-1">
                                                                            <p class="shell-text text-sm font-medium">{{ $item['label'] }}</p>
                                                                        </div>
                                                                        <div class="flex items-center gap-2">
                                                                            <p class="shell-text-info-strong shrink-0 font-mono text-[10px] uppercase tracking-[0.12em]">{{ $item['meta'] }}</p>
                                                                            <svg
                                                                                @class([
                                                                                    'h-3.5 w-3.5 shrink-0 shell-text-faint transition-transform',
                                                                                    'rotate-180' => $itemIndex === 0,
                                                                                ])
                                                                                data-node-item-caret
                                                                                viewBox="0 0 20 20"
                                                                                fill="none"
                                                                                aria-hidden="true"
                                                                            >
                                                                                <path d="M5 8 10 13 15 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                            </svg>
                                                                        </div>
                                                                    </button>

                                                                    <div
                                                                        data-node-item-panel
                                                                        @class([
                                                                            'shell-elevated rounded-[18px] px-3 py-3',
                                                                            'hidden' => $itemIndex !== 0,
                                                                        ])
                                                                    >
                                                                        <div class="space-y-3">
                                                                            <div class="flex flex-wrap gap-2">
                                                                                <x-desktop.status-pill :label="$group['label']" />
                                                                                <x-desktop.status-pill :label="$item['status']" />
                                                                            </div>
                                                                            <p class="shell-text-soft text-sm leading-6">{{ $item['summary'] }}</p>

                                                                            @if ($tab['key'] === 'open' && $assignableAgents->isNotEmpty())
                                                                                <div class="space-y-2">
                                                                                    <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Assign to agent</p>
                                                                                    <div class="flex items-center gap-2">
                                                                                        <select class="shell-surface shell-text min-w-0 flex-1 appearance-none rounded-full px-3 py-2 text-sm outline-none">
                                                                                            <option selected disabled>Choose an agent</option>
                                                                                            @foreach ($assignableAgents as $agent)
                                                                                                <option>{{ $agent['label'] }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                        <button
                                                                                            type="button"
                                                                                            class="shell-accent-chip inline-flex items-center justify-center rounded-full px-3 py-2 text-sm font-medium transition-colors hover:bg-[var(--shell-accent-hover)]"
                                                                                        >
                                                                                            Assign
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        </div>
                        </div>
                    </aside>
                    </div>

                    <button
                        type="button"
                        data-search-backdrop
                        class="shell-search-backdrop hidden absolute inset-0 z-10"
                        aria-label="Close search results"
                    ></button>
                    <button
                        type="button"
                        data-sidebar-backdrop
                        class="shell-overlay hidden absolute inset-0 z-20"
                        aria-label="Close sidebar overlay"
                    ></button>
                    <button
                        type="button"
                        data-right-rail-backdrop
                        class="shell-overlay hidden absolute inset-0 z-20"
                        aria-label="Close context panel overlay"
                    ></button>
                </div>
            @endif
        </main>

        <x-desktop.modal id="connection-creator-modal" title="Connections" description="Switch between this instance and your saved Katra servers.">
            <div class="space-y-5">
                <div class="space-y-2">
                    @foreach ($connectionLinks as $item)
                        <div class="shell-surface flex items-center gap-3 rounded-[20px] px-4 py-3">
                            <span class="shell-accent-chip flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-semibold uppercase tracking-[0.02em]">
                                {{ $item['prefix'] }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="shell-text truncate text-sm font-semibold">{{ $item['label'] }}</p>
                                <p class="shell-text-subtle mt-1 truncate text-sm">
                                    {{ $item['baseUrl'] ?: 'This instance' }}
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                @if ($item['active'])
                                    <p class="shell-text-info-strong text-sm font-medium">Current</p>
                                @else
                                    <form method="POST" action="{{ route('connections.activate', $item['id']) }}">
                                        @csrf

                                        <button type="submit" class="shell-icon-button inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors">
                                            <x-mdi-swap-horizontal class="h-4 w-4" />
                                            <span>{{ $item['authenticated'] ? 'Use' : 'Sign in' }}</span>
                                        </button>
                                    </form>
                                @endif

                                <button
                                    type="button"
                                    data-dialog-target="connection-editor-modal-{{ $item['id'] }}"
                                    class="shell-icon-button inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                                >
                                    <x-mdi-pencil class="h-4 w-4" />
                                    <span>Edit</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('connections.store') }}" class="space-y-4 border-t pt-5 shell-border">
                    @csrf

                    <div class="space-y-2">
                        <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Add a server</p>
                        <label for="connection-name" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Connection name</label>
                        <input
                            id="connection-name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]"
                            placeholder="Connection name (optional)"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="connection-base-url" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Server URL</label>
                        <input
                            id="connection-base-url"
                            name="base_url"
                            type="url"
                            value="{{ old('base_url') }}"
                            class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]"
                            placeholder="https://katra.example.com"
                            required
                        />
                        <p class="shell-text-faint text-sm leading-6">
                            Add a remote Katra server, then sign in only when you want to use it.
                        </p>
                    </div>

                    @if ($errors->has('name') || $errors->has('base_url'))
                        <div class="shell-danger-button rounded-[18px] px-4 py-3 text-sm">
                            <p>{{ $errors->first('name') ?: $errors->first('base_url') }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-1">
                        <button type="button" data-dialog-close class="shell-icon-button rounded-full px-4 py-2 text-sm font-medium transition-colors">
                            Close
                        </button>
                        <button type="submit" class="shell-accent-chip rounded-full px-4 py-2 text-sm font-medium transition-colors hover:bg-[var(--shell-accent-hover)]">
                            Add connection
                        </button>
                    </div>
                </form>
            </div>
        </x-desktop.modal>

        <x-desktop.modal id="workspace-creator-modal" title="Create workspace" description="Create a workspace for the current connection.">
            <form method="POST" action="{{ route('workspaces.store') }}" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <label for="workspace-name" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Workspace name</label>
                    <input
                        id="workspace-name"
                        name="workspace_name"
                        type="text"
                        value="{{ old('workspace_name') }}"
                        class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]"
                        placeholder="Project Atlas"
                        required
                    />
                </div>

                <p class="shell-text-faint text-sm leading-6">
                    Workspaces keep each connection organized by project.
                </p>

                @if ($errors->has('workspace_name'))
                    <div class="shell-danger-button rounded-[18px] px-4 py-3 text-sm">
                        <p>{{ $errors->first('workspace_name') }}</p>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="button" data-dialog-close class="shell-icon-button rounded-full px-4 py-2 text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="shell-accent-chip rounded-full px-4 py-2 text-sm font-medium transition-colors hover:bg-[var(--shell-accent-hover)]">
                        Create workspace
                    </button>
                </div>
            </form>
        </x-desktop.modal>

        @foreach ($connectionLinks as $item)
            <x-desktop.modal
                id="connection-editor-modal-{{ $item['id'] }}"
                :title="$item['isCurrentInstance'] ? 'Rename connection' : 'Edit connection'"
                :description="$item['isCurrentInstance']
                    ? 'Update the name shown for this instance on this device.'
                    : 'Update the name or server URL for this saved Katra connection.'"
            >
                <div class="space-y-5">
                    <form method="POST" action="{{ route('connections.update', $item['id']) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-2">
                            <label for="connection-edit-name-{{ $item['id'] }}" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Connection name</label>
                            <input
                                id="connection-edit-name-{{ $item['id'] }}"
                                name="name"
                                type="text"
                                value="{{ old('name', $item['label']) }}"
                                class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]"
                                placeholder="Connection name"
                            />
                        </div>

                        @unless ($item['isCurrentInstance'])
                            <div class="space-y-2">
                                <label for="connection-edit-base-url-{{ $item['id'] }}" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Server URL</label>
                                <input
                                    id="connection-edit-base-url-{{ $item['id'] }}"
                                    name="base_url"
                                    type="url"
                                    value="{{ old('base_url', $item['baseUrl']) }}"
                                    class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]"
                                    placeholder="https://katra.example.com"
                                    required
                                />
                            </div>
                        @endunless

                        @if ($item['accountEmail'])
                            <p class="shell-text-faint text-sm leading-6">
                                Signed in as {{ $item['accountEmail'] }}.
                            </p>
                        @endif

                        <div class="flex items-center justify-end gap-3 pt-1">
                            <button type="button" data-dialog-close class="shell-icon-button rounded-full px-4 py-2 text-sm font-medium transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="shell-accent-chip rounded-full px-4 py-2 text-sm font-medium transition-colors hover:bg-[var(--shell-accent-hover)]">
                                Save changes
                            </button>
                        </div>
                    </form>

                    @unless ($item['isCurrentInstance'])
                        <form method="POST" action="{{ route('connections.destroy', $item['id']) }}" class="border-t pt-5 shell-border">
                            @csrf
                            @method('DELETE')

                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="shell-text text-sm font-medium">Remove connection</p>
                                    <p class="shell-text-faint mt-1 text-sm leading-6">
                                        Delete this saved server from the device. You can add it again later.
                                    </p>
                                </div>

                                <button type="submit" class="shell-danger-button inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors">
                                    <x-mdi-trash-can class="h-4 w-4" />
                                    <span>Delete</span>
                                </button>
                            </div>
                        </form>
                    @endunless
                </div>
            </x-desktop.modal>
        @endforeach

        <x-desktop.modal id="room-creator-modal" title="Create room" description="Draft a new shared room for the current workspace. This is demo-only for now.">
            <form method="dialog" class="space-y-4">
                <div class="space-y-2">
                    <label for="room-name" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Room name</label>
                    <input id="room-name" type="text" value="operations-room" class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]" placeholder="team-room" />
                </div>

                <div class="space-y-2">
                    <label for="room-purpose" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Purpose</label>
                    <textarea id="room-purpose" rows="4" class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm leading-6 outline-none placeholder:text-[color:var(--shell-text-faint)]" placeholder="What kind of work happens here?">A shared room for coordination, updates, and linked work across the workspace.</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="button" data-dialog-close class="shell-icon-button rounded-full px-4 py-2 text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="shell-accent-chip rounded-full px-4 py-2 text-sm font-medium transition-colors hover:bg-[var(--shell-accent-hover)]">
                        Create room
                    </button>
                </div>
            </form>
        </x-desktop.modal>

        <x-desktop.modal id="chat-creator-modal" title="Start conversation" description="Select one or more contacts to open a conversation. This is demo-only for now.">
            <form method="dialog" class="space-y-4">
                <div data-contact-selector class="space-y-3">
                    <div class="space-y-2">
                        <label for="chat-contact-search" class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Contacts</label>
                        <input
                            id="chat-contact-search"
                            type="text"
                            data-contact-search
                            class="shell-input shell-text w-full rounded-[18px] px-4 py-3 text-sm outline-none placeholder:text-[color:var(--shell-text-faint)]"
                            placeholder="Search people, agents, and models"
                        />
                    </div>

                    <div class="space-y-2">
                        <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Selected</p>
                        <div data-contact-selected class="flex min-h-14 flex-wrap gap-2 rounded-[18px] px-3 py-3 shell-input">
                            <p data-contact-empty class="shell-text-subtle text-sm">No contacts selected yet.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Available contacts</p>
                        <div data-contact-options class="max-h-64 space-y-2 overflow-y-auto pr-1">
                            @foreach ($chatContacts as $contact)
                                <button
                                    type="button"
                                    data-contact-option
                                    data-contact-value="{{ $contact['value'] }}"
                                    data-contact-label="{{ $contact['label'] }}"
                                    class="shell-input flex w-full items-center gap-3 rounded-[18px] px-3 py-3 text-left transition-colors hover:bg-[var(--shell-elevated)]"
                                >
                                    <span @class([
                                        'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-mono text-sm uppercase',
                                        'shell-human-chip' => $contact['tone'] === 'human',
                                        'shell-bot-chip' => $contact['tone'] === 'bot',
                                    ])>
                                        {{ $contact['prefix'] }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="shell-text block truncate text-sm font-medium">{{ $contact['label'] }}</span>
                                        <span class="shell-text-faint block text-xs">{{ $contact['subtitle'] }}</span>
                                    </span>
                                    <span class="shell-text-info-strong text-xs font-medium">Add</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="button" data-dialog-close class="shell-icon-button rounded-full px-4 py-2 text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="shell-accent-chip rounded-full px-4 py-2 text-sm font-medium transition-colors hover:bg-[var(--shell-accent-hover)]">
                        Start conversation
                    </button>
                </div>
            </form>
        </x-desktop.modal>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const shell = document.querySelector('[data-desktop-shell]');

                if (! shell) {
                    return;
                }

                const grid = shell.querySelector('[data-shell-grid]');
                const sidebar = shell.querySelector('[data-sidebar]');
                const rightRail = shell.querySelector('[data-right-rail]');
                const backdrop = shell.querySelector('[data-sidebar-backdrop]');
                const rightRailBackdrop = shell.querySelector('[data-right-rail-backdrop]');
                const searchOverlay = shell.querySelector('[data-search-overlay]');
                const searchInput = shell.querySelector('[data-search-input]');
                const searchBackdrop = shell.querySelector('[data-search-backdrop]');
                const conversationStream = shell.querySelector('[data-conversation-stream]');
                const messageInput = shell.querySelector('[data-message-input]');
                const sendMessageButton = shell.querySelector('[data-send-message]');
                const attachFileButton = shell.querySelector('[data-attach-file]');
                const messageFileInput = shell.querySelector('[data-message-file-input]');
                const messageAttachments = shell.querySelector('[data-message-attachments]');
                const voiceToggleButton = shell.querySelector('[data-voice-toggle]');
                const voiceIndicator = shell.querySelector('[data-voice-indicator]');
                const profileMenu = shell.querySelector('[data-profile-menu]');
                const themeButtons = shell.querySelectorAll('[data-theme-option]');
                const collapseButtons = shell.querySelectorAll('[data-sidebar-toggle]');
                const expandButtons = shell.querySelectorAll('[data-sidebar-expand]');
                const rightRailToggleButtons = shell.querySelectorAll('[data-right-rail-toggle]');
                const rightRailCloseButtons = shell.querySelectorAll('[data-right-rail-close]');
                const rightRailPinButtons = shell.querySelectorAll('[data-right-rail-pin]');
                const rightRailResizeHandle = shell.querySelector('[data-right-rail-resize-handle]');
                const modals = document.querySelectorAll('[data-shell-modal]');
                const dialogButtons = document.querySelectorAll('[data-dialog-target]');
                const dialogCloseButtons = document.querySelectorAll('[data-dialog-close]');
                const navSectionButtons = shell.querySelectorAll('[data-nav-section-toggle]');
                const contactSelector = document.querySelector('[data-contact-selector]');
                const storageKey = 'katra.desktop.sidebar.preference';
                const rightRailStorageKey = 'katra.desktop.right-rail.preference';
                const rightRailPinStorageKey = 'katra.desktop.right-rail.pin';
                const rightRailWidthStorageKey = 'katra.desktop.right-rail.width';
                const conversationStorageKey = @json('katra.desktop.conversation.' . $activeWorkspace['slug']);
                const themeStorageKey = 'katra.desktop.theme.preference';
                const autoCollapseWidth = 1480;
                const rightRailAutoCollapseWidth = 1280;
                const rightRailMinWidth = 280;
                const rightRailMaxWidth = 560;
                const systemThemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
                const conversationSeedMessages = @json($conversationSeedMessages ?? []);
                const conversationResponders = @json($conversationResponders ?? []);
                const conversationMockReplies = @json($conversationMockReplies ?? []);

                let sidebarPreference = window.localStorage.getItem(storageKey);
                let rightRailPreference = window.localStorage.getItem(rightRailStorageKey);
                let rightRailPinned = window.localStorage.getItem(rightRailPinStorageKey) === 'true';
                let rightRailWidth = Number.parseInt(window.localStorage.getItem(rightRailWidthStorageKey) ?? '320', 10);
                let themePreference = window.localStorage.getItem(themeStorageKey) ?? 'system';
                let conversationMessages = [];
                let pendingAttachments = [];
                let voiceModeEnabled = false;
                let pendingResponseTimer = null;

                if (Number.isNaN(rightRailWidth)) {
                    rightRailWidth = 320;
                }

                rightRailWidth = Math.max(rightRailMinWidth, Math.min(rightRailMaxWidth, rightRailWidth));

                const resolveTheme = (preference) => {
                    if (preference === 'light' || preference === 'dark') {
                        return preference;
                    }

                    return systemThemeQuery.matches ? 'dark' : 'light';
                };

                const applyThemeState = () => {
                    document.documentElement.dataset.shellTheme = resolveTheme(themePreference);

                    themeButtons.forEach((button) => {
                        button.dataset.themeActive = button.dataset.themeOption === themePreference ? 'true' : 'false';
                    });
                };

                const conversationTimeFormatter = new Intl.DateTimeFormat([], {
                    hour: 'numeric',
                    minute: '2-digit',
                });

                const escapeHtml = (value) => String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                const conversationStorageAvailable = () => typeof window.sessionStorage !== 'undefined';

                const readConversationState = () => {
                    if (! conversationStorageAvailable()) {
                        return [...conversationSeedMessages];
                    }

                    const stored = window.sessionStorage.getItem(conversationStorageKey);

                    if (! stored) {
                        return [...conversationSeedMessages];
                    }

                    try {
                        const decoded = JSON.parse(stored);

                        return Array.isArray(decoded) ? decoded : [...conversationSeedMessages];
                    } catch (error) {
                        return [...conversationSeedMessages];
                    }
                };

                const persistConversationState = () => {
                    if (! conversationStorageAvailable()) {
                        return;
                    }

                    window.sessionStorage.setItem(conversationStorageKey, JSON.stringify(conversationMessages));
                };

                const timestampLabel = () => conversationTimeFormatter.format(new Date());

                const participantTone = (role) => {
                    if (role === 'Human') {
                        return 'shell-human-chip';
                    }

                    return 'shell-bot-chip';
                };

                const participantPrefix = (sender, role) => {
                    if (role === 'Human') {
                        return sender.slice(0, 1).toUpperCase();
                    }

                    return '@';
                };

                const roleTone = (role) => {
                    if (role === 'Agent') {
                        return 'text-[color:var(--shell-accent)]';
                    }

                    if (role === 'Model' || role === 'Worker') {
                        return 'shell-text-info-strong';
                    }

                    return 'shell-text-faint';
                };

                const attachmentMarkup = (attachments = []) => {
                    if (! Array.isArray(attachments) || attachments.length === 0) {
                        return '';
                    }

                    return `
                        <div class="mt-3 flex flex-wrap gap-2">
                            ${attachments.map((attachment) => `
                                <span class="shell-elevated inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm">
                                    <span class="shell-text">${escapeHtml(attachment.name)}</span>
                                    <span class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Mock</span>
                                </span>
                            `).join('')}
                        </div>
                    `;
                };

                const buildConversationMessage = (message) => {
                    const outgoing = message.direction === 'outgoing';
                    const bubbleTone = outgoing ? 'shell-accent-soft' : 'shell-elevated';
                    const align = outgoing ? 'justify-end' : 'justify-start';
                    const itemAlign = outgoing ? 'items-end' : 'items-start';
                    const avatarTone = participantTone(message.role);
                    const statusTone = roleTone(message.role);
                    const bodyMarkup = message.typing
                        ? `
                            <div class="shell-text-soft flex items-center gap-2 text-sm leading-7">
                                <span class="inline-flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[color:var(--shell-text-soft)]"></span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-[color:var(--shell-text-soft)]"></span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-[color:var(--shell-text-soft)]"></span>
                                </span>
                                <span>Thinking</span>
                            </div>
                        `
                        : message.body
                            ? `<p class="shell-text text-[15px] leading-7 whitespace-pre-wrap">${escapeHtml(message.body)}</p>`
                            : '';

                    return `
                        <article class="flex ${align}">
                            <div class="flex max-w-[82%] flex-col gap-2 ${itemAlign}">
                                <div class="flex items-center gap-2 px-1">
                                    ${outgoing ? '' : `
                                        <span class="${avatarTone} flex h-7 w-7 items-center justify-center rounded-full font-mono text-xs uppercase">
                                            ${escapeHtml(participantPrefix(message.sender, message.role))}
                                        </span>
                                    `}
                                    <span class="shell-text text-sm font-semibold">${escapeHtml(message.sender)}</span>
                                    <span class="font-mono text-[10px] uppercase tracking-[0.12em] ${statusTone}">${escapeHtml(message.role)}</span>
                                    <span class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">${escapeHtml(message.meta ?? '')}</span>
                                </div>

                                <div class="${bubbleTone} w-full rounded-[26px] px-4 py-3">
                                    ${bodyMarkup}
                                    ${attachmentMarkup(message.attachments)}
                                </div>
                            </div>
                        </article>
                    `;
                };

                const scrollConversationToBottom = () => {
                    if (! conversationStream) {
                        return;
                    }

                    window.requestAnimationFrame(() => {
                        conversationStream.scrollTop = conversationStream.scrollHeight;
                    });
                };

                const renderConversation = () => {
                    if (! conversationStream) {
                        return;
                    }

                    conversationStream.innerHTML = conversationMessages.map(buildConversationMessage).join('');
                    scrollConversationToBottom();
                };

                const syncComposerHeight = () => {
                    if (! messageInput) {
                        return;
                    }

                    messageInput.style.height = '0px';
                    messageInput.style.height = `${Math.min(messageInput.scrollHeight, 160)}px`;
                };

                const renderPendingAttachments = () => {
                    if (! messageAttachments) {
                        return;
                    }

                    if (pendingAttachments.length === 0) {
                        messageAttachments.classList.add('hidden');
                        messageAttachments.innerHTML = '';

                        return;
                    }

                    messageAttachments.classList.remove('hidden');
                    messageAttachments.innerHTML = pendingAttachments.map((attachment) => `
                        <button
                            type="button"
                            class="shell-elevated inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm transition-colors hover:bg-[var(--shell-surface)]"
                            data-remove-attachment="${escapeHtml(attachment.id)}"
                        >
                            <span class="shell-text">${escapeHtml(attachment.name)}</span>
                            <span class="shell-text-faint text-xs" aria-hidden="true">×</span>
                        </button>
                    `).join('');
                };

                const applyVoiceState = () => {
                    if (! voiceToggleButton) {
                        return;
                    }

                    voiceToggleButton.classList.toggle('shell-accent-chip', voiceModeEnabled);
                    voiceToggleButton.classList.toggle('shell-elevated', ! voiceModeEnabled);
                    voiceToggleButton.setAttribute('aria-pressed', voiceModeEnabled ? 'true' : 'false');

                    if (voiceIndicator) {
                        voiceIndicator.classList.toggle('hidden', ! voiceModeEnabled);
                        voiceIndicator.classList.toggle('flex', voiceModeEnabled);
                    }
                };

                const nextAgentReply = () => {
                    const responder = conversationResponders[Math.floor(Math.random() * conversationResponders.length)] ?? {
                        label: 'Katra Agent',
                        role: 'Agent',
                    };
                    const body = conversationMockReplies[Math.floor(Math.random() * conversationMockReplies.length)];

                    return {
                        id: `reply-${Date.now()}`,
                        sender: responder.label,
                        role: responder.role === 'Worker' ? 'Agent' : responder.role,
                        meta: timestampLabel(),
                        body,
                        direction: 'incoming',
                        attachments: [],
                    };
                };

                const queueMockResponse = () => {
                    if (pendingResponseTimer) {
                        window.clearTimeout(pendingResponseTimer);
                    }

                    const typingMessage = {
                        id: `typing-${Date.now()}`,
                        sender: conversationResponders[0]?.label ?? 'Katra Agent',
                        role: 'Agent',
                        meta: 'Thinking',
                        body: '',
                        direction: 'incoming',
                        attachments: [],
                        typing: true,
                    };

                    conversationMessages.push(typingMessage);
                    renderConversation();
                    persistConversationState();

                    pendingResponseTimer = window.setTimeout(() => {
                        conversationMessages = conversationMessages.filter((message) => ! message.typing);
                        conversationMessages.push(nextAgentReply());
                        renderConversation();
                        persistConversationState();
                    }, 850);
                };

                const submitConversationMessage = () => {
                    if (! messageInput) {
                        return;
                    }

                    const body = messageInput.value.trim();

                    if (body === '' && pendingAttachments.length === 0 && ! voiceModeEnabled) {
                        return;
                    }

                    const attachments = pendingAttachments.map((attachment) => ({
                        name: attachment.name,
                    }));

                    const outgoingMessage = {
                        id: `message-${Date.now()}`,
                        sender: 'You',
                        role: 'Human',
                        meta: timestampLabel(),
                        body: body === '' && voiceModeEnabled ? 'Voice mode selected for this reply.' : body,
                        direction: 'outgoing',
                        attachments,
                    };

                    conversationMessages.push(outgoingMessage);
                    messageInput.value = '';
                    pendingAttachments = [];
                    voiceModeEnabled = false;
                    renderPendingAttachments();
                    applyVoiceState();
                    syncComposerHeight();
                    renderConversation();
                    persistConversationState();
                    queueMockResponse();
                };

                const openSearchOverlay = () => {
                    searchOverlay?.classList.remove('hidden');
                    searchBackdrop?.classList.remove('hidden');
                };

                const closeSearchOverlay = () => {
                    searchOverlay?.classList.add('hidden');
                    searchBackdrop?.classList.add('hidden');
                };

                const resolveSidebarState = () => {
                    const autoCollapsed = window.innerWidth < autoCollapseWidth;
                    const collapsed = sidebarPreference === 'collapsed'
                        ? true
                        : sidebarPreference === 'expanded'
                            ? false
                            : autoCollapsed;

                    return {
                        collapsed,
                        overlayMode: autoCollapsed,
                    };
                };

                const resolveRightRailState = () => {
                    const autoCollapsed = window.innerWidth < rightRailAutoCollapseWidth;
                    const open = rightRailPreference === 'closed'
                        ? false
                        : rightRailPreference === 'open'
                            ? true
                            : ! autoCollapsed;
                    const pinned = rightRailPinned && ! autoCollapsed;

                    return {
                        open,
                        pinned,
                        overlayMode: open && ! pinned,
                    };
                };

                const applyShellGrid = () => {
                    const sidebarState = resolveSidebarState();
                    const rightRailState = resolveRightRailState();

                    let templateColumns = sidebarState.overlayMode
                        ? 'minmax(0, 1fr)'
                        : sidebarState.collapsed
                            ? '0px minmax(0, 1fr)'
                            : '320px minmax(0, 1fr)';

                    if (rightRailState.open && rightRailState.pinned && ! sidebarState.overlayMode) {
                        templateColumns += ` ${rightRailWidth}px`;
                    }

                    grid.style.gridTemplateColumns = templateColumns;
                };

                const applySidebarState = () => {
                    const { collapsed, overlayMode } = resolveSidebarState();

                    sidebar.classList.toggle('absolute', overlayMode);
                    sidebar.classList.toggle('inset-y-0', overlayMode);
                    sidebar.classList.toggle('left-0', overlayMode);
                    sidebar.classList.toggle('z-30', overlayMode);
                    sidebar.classList.toggle('w-[320px]', overlayMode);
                    sidebar.classList.toggle('max-w-[calc(100vw-96px)]', overlayMode);
                    sidebar.classList.toggle('shadow-2xl', overlayMode);

                    sidebar.style.opacity = collapsed ? '0' : '1';
                    sidebar.style.pointerEvents = collapsed ? 'none' : 'auto';
                    sidebar.style.transform = collapsed
                        ? 'translateX(-18px)'
                        : 'translateX(0)';

                    if (backdrop) {
                        backdrop.classList.toggle('hidden', ! overlayMode || collapsed);
                        backdrop.classList.toggle('block', overlayMode && ! collapsed);
                    }

                    collapseButtons.forEach((button) => {
                        button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    });

                    expandButtons.forEach((button) => {
                        button.classList.toggle('hidden', ! collapsed);
                        button.classList.toggle('inline-flex', collapsed);
                        button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    });
                };

                const applyRightRailState = () => {
                    if (! rightRail) {
                        return;
                    }

                    const { open, pinned, overlayMode } = resolveRightRailState();

                    rightRail.style.width = `${rightRailWidth}px`;
                    rightRail.style.opacity = open ? '1' : '0';
                    rightRail.style.pointerEvents = open ? 'auto' : 'none';
                    rightRail.style.transform = open ? 'translateX(0)' : 'translateX(24px)';

                    rightRail.classList.toggle('absolute', overlayMode || ! open || ! pinned);
                    rightRail.classList.toggle('inset-y-0', overlayMode || ! open || ! pinned);
                    rightRail.classList.toggle('right-0', overlayMode || ! open || ! pinned);
                    rightRail.classList.toggle('relative', open && pinned);
                    rightRail.classList.toggle('max-w-[calc(100vw-32px)]', overlayMode || ! pinned);
                    rightRail.classList.toggle('max-w-none', open && pinned);

                    if (rightRailBackdrop) {
                        rightRailBackdrop.classList.toggle('hidden', ! overlayMode);
                        rightRailBackdrop.classList.toggle('block', overlayMode);
                    }

                    rightRailToggleButtons.forEach((button) => {
                        const openIcon = button.querySelector('[data-right-rail-toggle-open-icon]');
                        const closeIcon = button.querySelector('[data-right-rail-toggle-close-icon]');

                        button.setAttribute('aria-expanded', open ? 'true' : 'false');
                        button.setAttribute('aria-label', open ? 'Close context panel' : 'Open context panel');
                        button.setAttribute('title', open ? 'Close context panel' : 'Open context panel');
                        openIcon?.classList.toggle('hidden', open);
                        closeIcon?.classList.toggle('hidden', ! open);
                    });

                    rightRailPinButtons.forEach((button) => {
                        button.setAttribute('aria-pressed', pinned ? 'true' : 'false');
                        button.setAttribute('aria-label', pinned ? 'Unpin context panel' : 'Pin context panel');
                        button.setAttribute('title', pinned ? 'Unpin context panel' : 'Pin context panel');
                    });
                };

                backdrop?.addEventListener('click', () => {
                    sidebarPreference = 'collapsed';
                    window.localStorage.setItem(storageKey, sidebarPreference);
                    applyShellGrid();
                    applySidebarState();
                });

                collapseButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        sidebarPreference = 'collapsed';
                        window.localStorage.setItem(storageKey, sidebarPreference);
                        applyShellGrid();
                        applySidebarState();
                    });
                });

                expandButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        sidebarPreference = 'expanded';
                        window.localStorage.setItem(storageKey, sidebarPreference);
                        applyShellGrid();
                        applySidebarState();
                    });
                });

                rightRailToggleButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const { open } = resolveRightRailState();

                        rightRailPreference = open ? 'closed' : 'open';
                        window.localStorage.setItem(rightRailStorageKey, rightRailPreference);
                        applyShellGrid();
                        applyRightRailState();
                    });
                });

                rightRailCloseButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        rightRailPreference = 'closed';
                        window.localStorage.setItem(rightRailStorageKey, rightRailPreference);
                        applyShellGrid();
                        applyRightRailState();
                    });
                });

                rightRailPinButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        rightRailPinned = ! rightRailPinned;
                        window.localStorage.setItem(rightRailPinStorageKey, rightRailPinned ? 'true' : 'false');
                        rightRailPreference = 'open';
                        window.localStorage.setItem(rightRailStorageKey, rightRailPreference);
                        applyShellGrid();
                        applyRightRailState();
                    });
                });

                rightRailBackdrop?.addEventListener('click', () => {
                    rightRailPreference = 'closed';
                    window.localStorage.setItem(rightRailStorageKey, rightRailPreference);
                    applyShellGrid();
                    applyRightRailState();
                });

                searchInput?.addEventListener('input', (event) => {
                    const query = event.target.value.trim();

                    if (query === '') {
                        closeSearchOverlay();

                        return;
                    }

                    openSearchOverlay();
                });

                searchInput?.addEventListener('focus', (event) => {
                    if (event.target.value.trim() !== '') {
                        openSearchOverlay();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (! profileMenu?.hasAttribute('open')) {
                        return;
                    }

                    if (event.target instanceof Node && ! profileMenu.contains(event.target)) {
                        profileMenu.removeAttribute('open');
                    }
                });

                document.addEventListener('click', (event) => {
                    if (! rightRail || ! (event.target instanceof Node)) {
                        return;
                    }

                    const { open, pinned } = resolveRightRailState();

                    if (! open || pinned) {
                        return;
                    }

                    const clickedToggle = Array.from(rightRailToggleButtons).some((button) => button.contains(event.target));

                    if (! rightRail.contains(event.target) && ! clickedToggle) {
                        rightRailPreference = 'closed';
                        window.localStorage.setItem(rightRailStorageKey, rightRailPreference);
                        applyShellGrid();
                        applyRightRailState();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (! searchOverlay || ! searchInput || ! (event.target instanceof Node)) {
                        return;
                    }

                    if (searchOverlay.classList.contains('hidden')) {
                        return;
                    }

                    if (! searchOverlay.contains(event.target) && ! searchInput.contains(event.target)) {
                        closeSearchOverlay();
                    }
                });

                searchBackdrop?.addEventListener('click', () => {
                    closeSearchOverlay();
                    searchInput?.blur();
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && searchOverlay && ! searchOverlay.classList.contains('hidden')) {
                        closeSearchOverlay();
                        searchInput?.blur();
                    }
                });

                rightRailResizeHandle?.addEventListener('pointerdown', (event) => {
                    event.preventDefault();

                    const handle = event.currentTarget;

                    if (! (handle instanceof HTMLElement)) {
                        return;
                    }

                    handle.setPointerCapture(event.pointerId);

                    const onPointerMove = (moveEvent) => {
                        rightRailWidth = Math.max(
                            rightRailMinWidth,
                            Math.min(rightRailMaxWidth, window.innerWidth - moveEvent.clientX),
                        );
                        window.localStorage.setItem(rightRailWidthStorageKey, String(rightRailWidth));
                        applyShellGrid();
                        applyRightRailState();
                    };

                    const onPointerUp = () => {
                        handle.removeEventListener('pointermove', onPointerMove);
                        handle.removeEventListener('pointerup', onPointerUp);
                        handle.removeEventListener('pointercancel', onPointerUp);
                    };

                    handle.addEventListener('pointermove', onPointerMove);
                    handle.addEventListener('pointerup', onPointerUp);
                    handle.addEventListener('pointercancel', onPointerUp);
                });

                modals.forEach((modal) => {
                    modal.addEventListener('click', (event) => {
                        if (event.target === modal) {
                            modal.close();
                        }
                    });
                });

                dialogButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const dialogId = button.getAttribute('data-dialog-target');

                        if (! dialogId) {
                            return;
                        }

                        document.getElementById(dialogId)?.showModal();
                    });
                });

                dialogCloseButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        button.closest('dialog')?.close();
                    });
                });

                navSectionButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const sectionId = button.getAttribute('data-nav-section-target');

                        if (! sectionId) {
                            return;
                        }

                        const content = document.getElementById(sectionId);
                        const expanded = button.getAttribute('aria-expanded') === 'true';

                        content?.classList.toggle('hidden', expanded);
                        button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                        button.classList.toggle('rotate-180', ! expanded);
                    });
                });

                if (contactSelector) {
                    const searchInput = contactSelector.querySelector('[data-contact-search]');
                    const selectedContainer = contactSelector.querySelector('[data-contact-selected]');
                    const emptyState = contactSelector.querySelector('[data-contact-empty]');
                    const optionButtons = Array.from(contactSelector.querySelectorAll('[data-contact-option]'));
                    const selectedContacts = new Map();

                    const renderSelectedContacts = () => {
                        if (! selectedContainer) {
                            return;
                        }

                        selectedContainer.querySelectorAll('[data-contact-chip]').forEach((chip) => chip.remove());

                        if (selectedContacts.size === 0) {
                            emptyState?.classList.remove('hidden');

                            return;
                        }

                        emptyState?.classList.add('hidden');

                        selectedContacts.forEach((contact) => {
                            const chip = document.createElement('button');
                            chip.type = 'button';
                            chip.dataset.contactChip = contact.value;
                            chip.className = 'shell-accent-soft inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium';
                            chip.innerHTML = `
                                <span>${contact.label}</span>
                                <span aria-hidden="true" class="shell-text-faint text-xs">×</span>
                            `;

                            chip.addEventListener('click', () => {
                                selectedContacts.delete(contact.value);
                                renderSelectedContacts();
                                renderContactOptions(searchInput?.value ?? '');
                            });

                            selectedContainer.appendChild(chip);
                        });
                    };

                    const renderContactOptions = (query = '') => {
                        const normalizedQuery = query.trim().toLowerCase();

                        optionButtons.forEach((button) => {
                            const value = button.dataset.contactValue ?? '';
                            const label = (button.dataset.contactLabel ?? '').toLowerCase();
                            const matchesQuery = normalizedQuery === '' || label.includes(normalizedQuery);
                            const selected = selectedContacts.has(value);

                            button.classList.toggle('hidden', ! matchesQuery);
                            button.classList.toggle('opacity-60', selected);
                            button.setAttribute('aria-pressed', selected ? 'true' : 'false');

                            const action = button.querySelector('.shell-text-info-strong');

                            if (action) {
                                action.textContent = selected ? 'Selected' : 'Add';
                            }
                        });
                    };

                    optionButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            const value = button.dataset.contactValue ?? '';
                            const label = button.dataset.contactLabel ?? '';

                            if (! value || ! label) {
                                return;
                            }

                            if (selectedContacts.has(value)) {
                                selectedContacts.delete(value);
                            } else {
                                selectedContacts.set(value, { value, label });
                            }

                            renderSelectedContacts();
                            renderContactOptions(searchInput?.value ?? '');
                        });
                    });

                    searchInput?.addEventListener('input', (event) => {
                        renderContactOptions(event.target.value ?? '');
                    });

                    renderSelectedContacts();
                    renderContactOptions();
                }

                shell.querySelectorAll('[data-node-tabs]').forEach((nodeTabs) => {
                    const buttons = Array.from(nodeTabs.querySelectorAll('[data-node-tab-button]'));
                    const panels = Array.from(nodeTabs.querySelectorAll('[data-node-tab-panel]'));

                    buttons.forEach((button) => {
                        button.addEventListener('click', () => {
                            const target = button.dataset.nodeTabTarget;

                            buttons.forEach((candidate) => {
                                const active = candidate === button;

                                candidate.classList.toggle('shell-accent-chip', active);
                                candidate.classList.toggle('shell-theme-option', ! active);
                                candidate.setAttribute('aria-pressed', active ? 'true' : 'false');
                            });

                            panels.forEach((panel) => {
                                panel.classList.toggle('hidden', panel.dataset.nodeTabPanel !== target);
                            });
                        });
                    });

                    nodeTabs.querySelectorAll('[data-node-item]').forEach((item) => {
                        const button = item.querySelector('[data-node-item-button]');
                        const panel = item.querySelector('[data-node-item-panel]');
                        const caret = item.querySelector('[data-node-item-caret]');

                        button?.addEventListener('click', () => {
                            const expanded = button.getAttribute('aria-expanded') === 'true';

                            button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                            panel?.classList.toggle('hidden', expanded);
                            caret?.classList.toggle('rotate-180', ! expanded);
                        });
                    });
                });

                conversationMessages = readConversationState();
                persistConversationState();
                renderConversation();
                renderPendingAttachments();
                applyVoiceState();
                syncComposerHeight();

                messageInput?.addEventListener('input', () => {
                    syncComposerHeight();
                });

                messageInput?.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' && ! event.shiftKey) {
                        event.preventDefault();
                        submitConversationMessage();
                    }
                });

                sendMessageButton?.addEventListener('click', () => {
                    submitConversationMessage();
                });

                attachFileButton?.addEventListener('click', () => {
                    messageFileInput?.click();
                });

                messageFileInput?.addEventListener('change', (event) => {
                    const target = event.target;

                    if (! (target instanceof HTMLInputElement) || ! target.files) {
                        return;
                    }

                    pendingAttachments = [
                        ...pendingAttachments,
                        ...Array.from(target.files).map((file, index) => ({
                            id: `${Date.now()}-${index}-${file.name}`,
                            name: file.name,
                        })),
                    ];

                    renderPendingAttachments();
                    target.value = '';
                });

                messageAttachments?.addEventListener('click', (event) => {
                    const target = event.target;

                    if (! (target instanceof HTMLElement)) {
                        return;
                    }

                    const removeButton = target.closest('[data-remove-attachment]');

                    if (! removeButton) {
                        return;
                    }

                    const attachmentId = removeButton.getAttribute('data-remove-attachment');

                    pendingAttachments = pendingAttachments.filter((attachment) => attachment.id !== attachmentId);
                    renderPendingAttachments();
                });

                voiceToggleButton?.addEventListener('click', () => {
                    voiceModeEnabled = ! voiceModeEnabled;
                    applyVoiceState();
                });

                themeButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        themePreference = button.dataset.themeOption ?? 'system';
                        window.localStorage.setItem(themeStorageKey, themePreference);
                        applyThemeState();
                    });
                });

                systemThemeQuery.addEventListener('change', () => {
                    if (themePreference === 'system') {
                        applyThemeState();
                    }
                });

                window.addEventListener('resize', () => {
                    applyShellGrid();
                    applySidebarState();
                    applyRightRailState();
                });

                applyThemeState();
                applyShellGrid();
                applySidebarState();
                applyRightRailState();
            });
        </script>
    </body>
</html>
