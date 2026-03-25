<?php

namespace App\Http\Controllers;

use App\Features\Desktop\MvpShell;
use App\Models\InstanceConnection;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Surreal\SurrealRuntimeManager;
use App\Support\Connections\InstanceConnectionManager;
use App\Support\Features\DesktopUi;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class HomeController extends Controller
{
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
    private function activeWorkspaceState(InstanceConnection $activeConnection, bool $localReady): array
    {
        if ($activeConnection->kind === InstanceConnection::KIND_SERVER) {
            return [
                'slug' => 'remote-instance',
                'label' => $activeConnection->name,
                'meta' => $this->connectionMeta($activeConnection),
                'prefix' => $this->connectionPrefix($activeConnection),
                'summary' => sprintf(
                    'A connected server workspace for shared orchestration, worker presence, and linked team context on %s.',
                    $activeConnection->name,
                ),
                'room' => '# relay-ops',
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
            'slug' => 'current-instance',
            'label' => $activeConnection->name,
            'meta' => $this->connectionMeta($activeConnection),
            'prefix' => $this->connectionPrefix($activeConnection),
            'summary' => $localReady
                ? 'The embedded Surreal runtime is available and the primary workspace is ready.'
                : 'A primary workspace on this instance for conversations, tasks, artifacts, and decisions.',
            'room' => '# design-room',
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
            ->map(fn (InstanceConnection $connection): array => [
                'id' => (int) $connection->getKey(),
                'label' => $connection->name,
                'meta' => $this->connectionMeta($connection),
                'active' => (int) $connection->getKey() === (int) $activeConnection->getKey(),
                'prefix' => $this->connectionPrefix($connection),
                'baseUrl' => $connection->base_url,
                'authenticated' => $connection->is_authenticated,
                'accountEmail' => $connection->user?->email,
                'isCurrentInstance' => $connection->is_current_instance,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array{
     *     room: string,
     *     participants: array<int, array{label: string, meta: string}>
     * }  $workspace
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string}>
     */
    private function favoriteLinks(array $workspace, string $viewerName): array
    {
        $favorites = [
            ['label' => $workspace['room'], 'active' => true, 'prefix' => '#', 'tone' => 'room'],
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
     * @param  array<int, array{label: string, meta: string}>  $participants
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string}>
     */
    private function chatLinks(array $participants, string $viewerName): array
    {
        $links = [
            ['label' => $viewerName, 'prefix' => substr($viewerName, 0, 1), 'tone' => 'human'],
        ];

        foreach ($participants as $participant) {
            if ($participant['meta'] === 'Human') {
                continue;
            }

            $links[] = [
                'label' => $participant['label'],
                'prefix' => '@',
                'tone' => 'bot',
            ];
        }

        return array_slice($links, 0, 3);
    }

    /**
     * @param  array<int, array{label: string, meta: string}>  $participants
     * @return array<int, array{
     *     label: string,
     *     value: string,
     *     prefix: string,
     *     tone: string,
     *     subtitle: string
     * }>
     */
    private function chatContacts(array $participants, string $viewerName): array
    {
        $contacts = [[
            'label' => $viewerName,
            'value' => str($viewerName)->slug()->value(),
            'prefix' => substr($viewerName, 0, 1),
            'tone' => 'human',
            'subtitle' => 'Human',
        ]];

        foreach ($participants as $participant) {
            if ($participant['meta'] === 'Human') {
                continue;
            }

            $contacts[] = [
                'label' => $participant['label'],
                'value' => str($participant['label'])->slug()->value(),
                'prefix' => '@',
                'tone' => 'bot',
                'subtitle' => $participant['meta'],
            ];
        }

        return $contacts;
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
    ): View|RedirectResponse {
        $localReady = false;
        $desktopUiStates = DesktopUi::states();
        $mvpShellEnabled = DesktopUi::enabled($desktopUiStates, MvpShell::class);

        try {
            if ($runtimeManager->ensureReady()) {
                Workspace::desktopPreview();
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

        $viewerIdentity = $this->viewerIdentity($request->user(), $activeConnection);
        $viewerName = $viewerIdentity['name'];

        if ($activeConnection->kind === InstanceConnection::KIND_SERVER && ! $activeConnection->is_authenticated) {
            return to_route('connections.connect', $activeConnection);
        }

        $connections = $connectionManager->connectionsFor($request->user(), $request->root());
        $activeWorkspace = $this->activeWorkspaceState($activeConnection, $localReady);

        return view('welcome', [
            'mvpShellEnabled' => $mvpShellEnabled,
            'activeConnection' => $activeConnection,
            'connectionLinks' => $this->connectionLinks($connections, $activeConnection),
            'activeWorkspace' => $activeWorkspace,
            'favoriteLinks' => $this->favoriteLinks($activeWorkspace, $viewerName),
            'roomLinks' => $this->roomLinks($activeConnection, $activeWorkspace['room']),
            'chatLinks' => $this->chatLinks($activeWorkspace['participants'], $viewerName),
            'chatContacts' => $this->chatContacts($activeWorkspace['participants'], $viewerName),
            'conversationNodeTabs' => $this->conversationNodeTabs($activeWorkspace),
            'messages' => $activeWorkspace['messages'],
            'participants' => $activeWorkspace['participants'],
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

    /**
     * @return array{name: string, email: string, initials: string}
     */
    private function viewerIdentity(?User $viewer, InstanceConnection $activeConnection): array
    {
        $remoteIdentity = $activeConnection->kind === InstanceConnection::KIND_SERVER
            ? data_get($activeConnection->session_context, 'user')
            : null;
        $remoteEmail = $activeConnection->kind === InstanceConnection::KIND_SERVER
            ? data_get($activeConnection->session_context, 'email')
            : null;
        $remoteName = data_get($remoteIdentity, 'name');

        if ((! is_string($remoteName) || $remoteName === '') && is_string($remoteEmail) && $remoteEmail !== '') {
            $remoteName = $this->nameFromEmail($remoteEmail);
        }

        $name = $remoteName
            ?: $viewer?->name
            ?: 'Derek Bourgeois';

        $email = data_get($remoteIdentity, 'email')
            ?: $remoteEmail
            ?: $viewer?->email
            ?: 'derek@katra.io';

        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => strtoupper(substr($segment, 0, 1)))
            ->implode('');

        return [
            'name' => $name,
            'email' => $email,
            'initials' => $initials !== '' ? $initials : 'K',
        ];
    }

    private function nameFromEmail(string $email): string
    {
        $localPart = (string) str($email)->before('@');
        $segments = preg_split('/[._-]+/', $localPart) ?: [];
        $segments = array_values(array_filter(array_map(
            fn (string $segment): string => str($segment)->title()->value(),
            $segments,
        )));

        $firstName = $segments[0] ?? 'Remote';
        $lastName = count($segments) > 1 ? implode(' ', array_slice($segments, 1)) : 'User';

        return trim($firstName.' '.$lastName);
    }
}
