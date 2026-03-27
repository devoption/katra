<?php

namespace App\Http\Controllers;

use App\Features\Desktop\MvpShell;
use App\Models\InstanceConnection;
use App\Models\SurrealWorkspace;
use App\Models\Workspace;
use App\Models\WorkspaceAgent;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatMessage;
use App\Models\WorkspaceChatParticipant;
use App\Services\Surreal\SurrealRuntimeManager;
use App\Support\Chats\WorkspaceAgentManager;
use App\Support\Chats\WorkspaceChatManager;
use App\Support\Connections\InstanceConnectionManager;
use App\Support\Connections\ViewerIdentityResolver;
use App\Support\Features\DesktopUi;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class HomeController extends Controller
{
    private const FAVORITES_ENABLED = false;

    /**
     * @return array{
     *     slug: string,
     *     label: string,
     *     meta: string,
     *     prefix: string,
     *     summary: string,
     *     room: string,
     *     roomStatus: string,
     *     participants: array<int, array{label: string, meta: string}>,
     *     notes: array<int, string>,
     *     tasks: array<int, array{label: string, status: string, summary: string}>,
     *     artifacts: array<int, array{label: string, kind: string, summary: string}>,
     *     decisions: array<int, array{label: string, owner: string, summary: string}>,
     *     messages: array<int, array{speaker: string, role: string, body: string, meta: string, tone: string}>
     * }
     */
    private function activeWorkspaceState(InstanceConnection $activeConnection, Workspace $workspace, bool $localReady): array
    {
        if ($activeConnection->kind === InstanceConnection::KIND_SERVER) {
            return [
                'slug' => $workspace->slug,
                'label' => $workspace->name,
                'meta' => $activeConnection->name,
                'prefix' => strtoupper(substr($workspace->name, 0, 1)),
                'summary' => $workspace->summary ?: sprintf(
                    '%s is the active workspace on %s for shared orchestration, worker presence, and linked team context.',
                    $workspace->name,
                    $activeConnection->name,
                ),
                'room' => '# general',
                'roomStatus' => 'remote',
                'participants' => [
                    ['label' => 'You', 'meta' => 'Human'],
                    ['label' => 'Ops Agent', 'meta' => 'Worker'],
                    ['label' => 'Team Model', 'meta' => 'Model'],
                    ['label' => 'Routing Agent', 'meta' => 'Worker'],
                ],
                'notes' => [
                    'Remote work should preserve the same room model as local work.',
                    'The desktop app can be a client and a worker without splitting the product model in two.',
                    'Shared AI workloads should flow through a dedicated worker queue, not the default queue.',
                ],
                'tasks' => [
                    [
                        'label' => 'Stage remote workers',
                        'status' => 'Queued',
                        'summary' => 'Make room for authenticated desktop workers contributing model capacity to a server instance.',
                    ],
                    [
                        'label' => 'Define secure payloads',
                        'status' => 'Draft',
                        'summary' => 'Keep worker jobs minimized, private, and cleaned up after completion.',
                    ],
                ],
                'artifacts' => [
                    [
                        'label' => 'Queue boundary notes',
                        'kind' => 'Guide',
                        'summary' => 'Remote AI work should use a dedicated worker queue, separate from normal Laravel jobs.',
                    ],
                ],
                'decisions' => [
                    [
                        'label' => 'Same product model',
                        'owner' => 'Architecture',
                        'summary' => 'Local and remote should be different runtimes of the same Katra model, not separate apps.',
                    ],
                ],
                'messages' => [
                    [
                        'speaker' => 'You',
                        'role' => 'Human',
                        'tone' => 'plain',
                        'meta' => 'Latest',
                        'body' => 'A remote instance should still feel like the same workspace model, not a separate product mode.',
                    ],
                    [
                        'speaker' => 'Ops Agent',
                        'role' => 'Agent',
                        'tone' => 'accent',
                        'meta' => 'Queued',
                        'body' => 'Desktop workers can stay available to authenticated instances while the main queue remains server-owned.',
                    ],
                ],
            ];
        }

        return [
            'slug' => $workspace->slug,
            'label' => $workspace->name,
            'meta' => $activeConnection->name,
            'prefix' => strtoupper(substr($workspace->name, 0, 1)),
            'summary' => $workspace->summary ?: ($localReady
                ? sprintf('The embedded Surreal runtime is available and %s is ready.', $workspace->name)
                : sprintf('%s is a workspace on this instance for conversations, tasks, artifacts, and decisions.', $workspace->name)),
            'room' => '# general',
            'roomStatus' => $localReady ? 'ready' : 'draft',
            'participants' => [
                ['label' => 'You', 'meta' => 'Human'],
                ['label' => 'Planner Agent', 'meta' => 'Worker'],
                ['label' => 'Research Model', 'meta' => 'Model'],
                ['label' => 'Context Agent', 'meta' => 'Worker'],
            ],
            'notes' => [
                'The core room should feel durable enough that work keeps accumulating here over time.',
                'Tasks, artifacts, and decisions should stay linked without overtaking the room itself.',
                'The first desktop shell should stay generic enough to seed other local-first products later on.',
            ],
            'tasks' => [
                [
                    'label' => 'Shape the MVP shell',
                    'status' => 'In review',
                    'summary' => 'Tighten the room layout, spacing, and navigation so the shell feels like an app instead of a staged page.',
                ],
                [
                    'label' => 'Introduce connection switching',
                    'status' => 'Queued',
                    'summary' => 'Make room for local and remote instances without changing the top-level layout.',
                ],
                [
                    'label' => 'Refine linked work',
                    'status' => 'Draft',
                    'summary' => 'Keep tasks, artifacts, and decisions close to the room without turning the whole shell into a dashboard.',
                ],
            ],
            'artifacts' => [
                [
                    'label' => 'Room layout',
                    'kind' => 'Note',
                    'summary' => 'Current draft for the center room and right-side context split.',
                ],
                [
                    'label' => 'Brand guide',
                    'kind' => 'Guide',
                    'summary' => 'Nord palette, quieter type, and the current Katra identity rules.',
                ],
            ],
            'decisions' => [
                [
                    'label' => 'Persistent rooms',
                    'owner' => 'Product',
                    'summary' => 'One room per participant set should replace disposable transcript threads.',
                ],
                [
                    'label' => 'Diagnostics stay hidden',
                    'owner' => 'Desktop',
                    'summary' => 'Connection and runtime details belong in logs or a developer view, not in the main shell.',
                ],
            ],
            'messages' => [
                [
                    'speaker' => 'You',
                    'role' => 'Human',
                    'tone' => 'plain',
                    'meta' => 'Just now',
                    'body' => 'This room should feel like the place where work already lives, not like a landing page that still needs to explain itself.',
                ],
                [
                    'speaker' => 'Planner Agent',
                    'role' => 'Agent',
                    'tone' => 'accent',
                    'meta' => 'Note',
                    'body' => 'Keep the center focused on the room. Tasks, artifacts, and decisions can stay visible in the context rail without taking over the main flow.',
                ],
                [
                    'speaker' => 'Research Model',
                    'role' => 'Model',
                    'tone' => 'subtle',
                    'meta' => 'Draft',
                    'body' => 'The layout should stay generic enough to support other local-first products later: rooms on the left, active work in the center, linked context on the right.',
                ],
            ],
        ];
    }

    /**
     * @param  EloquentCollection<int, InstanceConnection>  $connections
     * @return array<int, array{
     *     id: int,
     *     label: string,
     *     meta: string,
     *     active: bool,
     *     prefix: string,
     *     baseUrl: string|null,
     *     authenticated: bool,
     *     accountEmail: string|null,
     *     isCurrentInstance: bool
     * }>
     */
    private function connectionLinks(EloquentCollection $connections, InstanceConnection $activeConnection): array
    {
        return $connections
            ->map(function (InstanceConnection $connection) use ($activeConnection): array {
                $remoteEmail = $connection->kind === InstanceConnection::KIND_SERVER
                    ? data_get($connection->session_context, 'user.email') ?? data_get($connection->session_context, 'email')
                    : null;

                return [
                    'id' => (int) $connection->getKey(),
                    'label' => $connection->name,
                    'meta' => $this->connectionMeta($connection),
                    'active' => (int) $connection->getKey() === (int) $activeConnection->getKey(),
                    'prefix' => $this->connectionPrefix($connection),
                    'baseUrl' => $connection->base_url,
                    'authenticated' => $connection->is_authenticated,
                    'accountEmail' => is_string($remoteEmail) && $remoteEmail !== '' ? $remoteEmail : $connection->user?->email,
                    'isCurrentInstance' => $connection->is_current_instance,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  EloquentCollection<int, Workspace>  $workspaces
     * @return array<int, array{id: int, label: string, prefix: string, active: bool, tone: string}>
     */
    private function workspaceLinks(EloquentCollection $workspaces, Workspace $activeWorkspace): array
    {
        return $workspaces
            ->map(fn (Workspace $workspace): array => [
                'id' => (int) $workspace->getKey(),
                'label' => $workspace->name,
                'prefix' => strtoupper(substr($workspace->name, 0, 1)),
                'active' => (int) $workspace->getKey() === (int) $activeWorkspace->getKey(),
                'tone' => 'room',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array{
     *     label: string,
     *     participants: array<int, array{label: string, meta: string}>
     * }  $workspace
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string}>
     */
    private function favoriteLinks(array $workspace, string $viewerName): array
    {
        $favorites = [
            ['label' => $workspace['label'], 'active' => true, 'prefix' => $workspace['prefix'], 'tone' => 'room'],
            ['label' => $viewerName, 'prefix' => substr($viewerName, 0, 1), 'tone' => 'human'],
        ];

        foreach ($workspace['participants'] as $participant) {
            if ($participant['meta'] === 'Human') {
                continue;
            }

            $favorites[] = [
                'label' => $participant['label'],
                'prefix' => '@',
                'tone' => 'bot',
            ];

            break;
        }

        return $favorites;
    }

    /**
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string}>
     */
    private function roomLinks(InstanceConnection $activeConnection, string $activeRoom): array
    {
        if ($activeConnection->kind === InstanceConnection::KIND_SERVER) {
            return [
                ['label' => $activeRoom, 'active' => true, 'prefix' => '#', 'tone' => 'room'],
                ['label' => '# worker-queue', 'prefix' => '#', 'tone' => 'room'],
                ['label' => 'Operators', 'prefix' => 'O', 'tone' => 'human'],
            ];
        }

        return [
            ['label' => $activeRoom, 'active' => true, 'prefix' => '#', 'tone' => 'room'],
            ['label' => '# product-direction', 'prefix' => '#', 'tone' => 'room'],
            ['label' => 'Founders', 'prefix' => 'F', 'tone' => 'human'],
        ];
    }

    /**
     * @param  EloquentCollection<int, WorkspaceChat>  $chats
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string, meta?: string|null, action?: string|null}>
     */
    private function chatLinks(EloquentCollection $chats, WorkspaceChat $activeChat): array
    {
        return $chats
            ->map(function (WorkspaceChat $chat) use ($activeChat): array {
                $hasAgentParticipant = $chat->participants->contains(
                    fn (WorkspaceChatParticipant $participant): bool => $participant->participant_type === WorkspaceChatParticipant::TYPE_AGENT,
                );

                return [
                    'label' => $chat->name,
                    'active' => (int) $chat->getKey() === (int) $activeChat->getKey(),
                    'prefix' => $hasAgentParticipant
                        ? 'AI'
                        : ($chat->kind === WorkspaceChat::KIND_DIRECT ? '@' : strtoupper(substr($chat->name, 0, 1))),
                    'tone' => $hasAgentParticipant
                        ? 'bot'
                        : ($chat->kind === WorkspaceChat::KIND_DIRECT ? 'human' : 'room'),
                    'meta' => $hasAgentParticipant ? 'Agent' : null,
                    'action' => (int) $chat->getKey() === (int) $activeChat->getKey() ? null : route('chats.activate', $chat),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, meta: string}>
     */
    private function chatParticipants(WorkspaceChat $chat): array
    {
        return $chat->participants
            ->map(fn (WorkspaceChatParticipant $participant): array => [
                'label' => $participant->display_name,
                'meta' => $participant->participant_type === WorkspaceChatParticipant::TYPE_AGENT ? 'Agent' : 'Human',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{speaker: string, role: string, body: string, meta: string, tone: string}>
     */
    private function chatMessages(WorkspaceChat $chat): array
    {
        return $chat->messages()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(function (WorkspaceChatMessage $message): array {
                $role = match ($message->sender_type) {
                    WorkspaceChatMessage::SENDER_AGENT => 'Agent',
                    WorkspaceChatMessage::SENDER_SYSTEM => 'System',
                    default => 'Human',
                };

                return [
                    'speaker' => $message->sender_name,
                    'role' => $role,
                    'body' => $message->body,
                    'meta' => $message->created_at?->diffForHumans() ?? 'Just now',
                    'tone' => $role === 'Human' ? 'plain' : 'accent',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array{
     *     tasks: array<int, array{label: string, status: string, summary: string}>,
     *     artifacts: array<int, array{label: string, kind: string, summary: string}>,
     *     decisions: array<int, array{label: string, owner: string, summary: string}>
     * }  $workspace
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     groups: array<int, array{
     *         label: string,
     *         items: array<int, array{
     *             label: string,
     *             meta: string,
     *             status: string,
     *             summary: string
     *         }>
     *     }>
     * }>
     */
    private function conversationNodeTabs(array $workspace): array
    {
        $openTasks = collect($workspace['tasks'])
            ->take(2)
            ->map(fn (array $task): array => [
                'label' => $task['label'],
                'meta' => $task['status'],
                'status' => $task['status'],
                'summary' => $task['summary'],
            ])
            ->values()
            ->all();

        $openArtifacts = collect($workspace['artifacts'])
            ->take(1)
            ->map(fn (array $artifact): array => [
                'label' => $artifact['label'],
                'meta' => $artifact['kind'],
                'status' => 'Open',
                'summary' => $artifact['summary'],
            ])
            ->values()
            ->all();

        $openDecisions = collect($workspace['decisions'])
            ->take(1)
            ->map(fn (array $decision): array => [
                'label' => $decision['label'],
                'meta' => $decision['owner'],
                'status' => 'Open',
                'summary' => $decision['summary'],
            ])
            ->values()
            ->all();

        $closedTasks = collect($workspace['tasks'])
            ->slice(2, 1)
            ->map(fn (array $task): array => [
                'label' => $task['label'],
                'meta' => 'Closed',
                'status' => 'Closed',
                'summary' => $task['summary'],
            ])
            ->values()
            ->all();

        $closedArtifacts = collect($workspace['artifacts'])
            ->slice(1, 1)
            ->map(fn (array $artifact): array => [
                'label' => $artifact['label'],
                'meta' => 'Archived',
                'status' => 'Archived',
                'summary' => $artifact['summary'],
            ])
            ->values()
            ->all();

        $closedDecisions = collect($workspace['decisions'])
            ->slice(1, 1)
            ->map(fn (array $decision): array => [
                'label' => $decision['label'],
                'meta' => 'Settled',
                'status' => 'Settled',
                'summary' => $decision['summary'],
            ])
            ->values()
            ->all();

        return [
            [
                'key' => 'open',
                'label' => 'Open',
                'groups' => [
                    ['label' => 'Tasks', 'items' => $openTasks],
                    ['label' => 'Artifacts', 'items' => $openArtifacts],
                    ['label' => 'Decisions', 'items' => $openDecisions],
                ],
            ],
            [
                'key' => 'closed',
                'label' => 'Closed',
                'groups' => [
                    ['label' => 'Tasks', 'items' => $closedTasks],
                    ['label' => 'Artifacts', 'items' => $closedArtifacts],
                    ['label' => 'Decisions', 'items' => $closedDecisions],
                ],
            ],
        ];
    }

    public function __invoke(
        Request $request,
        SurrealRuntimeManager $runtimeManager,
        InstanceConnectionManager $connectionManager,
        ViewerIdentityResolver $viewerIdentityResolver,
        WorkspaceAgentManager $workspaceAgentManager,
        WorkspaceChatManager $chatManager,
    ): View|RedirectResponse {
        $localReady = false;
        $desktopUiStates = DesktopUi::states();
        $mvpShellEnabled = DesktopUi::enabled($desktopUiStates, MvpShell::class);

        try {
            if ($runtimeManager->ensureReady()) {
                SurrealWorkspace::desktopPreview();
                $localReady = true;
            }
        } catch (Throwable $exception) {
            if (! $exception instanceof RuntimeException) {
                report($exception);
            }
        }

        $activeConnection = $connectionManager->activeConnectionFor(
            $request->user(),
            $request->root(),
            $request->session(),
        );

        $viewerIdentity = $viewerIdentityResolver->resolve($request->user(), $activeConnection);
        $viewerName = $viewerIdentity['name'];

        if ($activeConnection->kind === InstanceConnection::KIND_SERVER && ! $activeConnection->is_authenticated) {
            return to_route('connections.connect', $activeConnection);
        }

        $connections = $connectionManager->connectionsFor($request->user());
        $workspaces = $connectionManager->workspacesFor($activeConnection);
        $activeWorkspaceModel = $connectionManager->activeWorkspaceFor($activeConnection, $workspaces);
        $availableAgents = $workspaceAgentManager->agentsFor($activeWorkspaceModel);
        $activeWorkspace = $this->activeWorkspaceState($activeConnection, $activeWorkspaceModel, $localReady);
        $activeChatModel = $chatManager->activeChatFor($activeWorkspaceModel, $request->user(), $viewerIdentity);
        $chats = $chatManager->chatsFor($activeWorkspaceModel);
        $participants = $this->chatParticipants($activeChatModel);
        $messages = $this->chatMessages($activeChatModel);
        $chatSubmissionToken = (string) Str::uuid();

        $request->session()->put('chat.create_token', $chatSubmissionToken);

        return view('welcome', [
            'mvpShellEnabled' => $mvpShellEnabled,
            'activeConnection' => $activeConnection,
            'connectionLinks' => $this->connectionLinks($connections, $activeConnection),
            'favoritesEnabled' => self::FAVORITES_ENABLED,
            'workspaceLinks' => $this->workspaceLinks($workspaces, $activeWorkspaceModel),
            'activeWorkspace' => $activeWorkspace,
            'activeChat' => $activeChatModel,
            'favoriteLinks' => $this->favoriteLinks($activeWorkspace, $viewerName),
            'roomLinks' => $this->roomLinks($activeConnection, $activeWorkspace['room']),
            'chatLinks' => $this->chatLinks($chats, $activeChatModel),
            'conversationNodeTabs' => $this->conversationNodeTabs($activeWorkspace),
            'messages' => $messages,
            'participants' => $participants,
            'chatSubmissionToken' => $chatSubmissionToken,
            'availableAgents' => $availableAgents->map(fn (WorkspaceAgent $workspaceAgent): array => [
                'id' => (int) $workspaceAgent->getKey(),
                'name' => $workspaceAgent->name,
            ])->values()->all(),
            'viewerName' => $viewerIdentity['name'],
            'viewerEmail' => $viewerIdentity['email'],
            'viewerInitials' => $viewerIdentity['initials'],
        ]);
    }

    private function connectionMeta(InstanceConnection $connection): string
    {
        if ($connection->kind === InstanceConnection::KIND_CURRENT_INSTANCE) {
            return 'This instance';
        }

        if (! $connection->is_authenticated) {
            return 'Sign in';
        }

        return parse_url((string) $connection->base_url, PHP_URL_HOST) ?: 'Server';
    }

    private function connectionPrefix(InstanceConnection $connection): string
    {
        return strtoupper(substr($connection->name, 0, 1));
    }
}
