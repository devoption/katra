<?php

namespace App\Http\Controllers;

use App\Features\Desktop\AgentPresence;
use App\Features\Desktop\ArtifactSurfaces;
use App\Features\Desktop\ConversationChannels;
use App\Features\Desktop\MvpShell;
use App\Features\Desktop\TaskSurfaces;
use App\Features\Desktop\WorkspaceNavigation;
use App\Models\Workspace;
use App\Services\Surreal\SurrealRuntimeManager;
use App\Support\Features\DesktopUi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class HomeController extends Controller
{
    /**
     * @return array<string, array{
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
     * }>
     */
    private function workspaces(?Workspace $workspace, bool $localReady): array
    {
        return [
            'katra-local' => [
                'slug' => 'katra-local',
                'label' => 'Katra Local',
                'meta' => 'Local',
                'prefix' => 'K',
                'summary' => $localReady
                    ? 'A local-first workspace on this device with the embedded Surreal runtime already available.'
                    : 'A local-first workspace on this device for conversations, tasks, artifacts, and decisions.',
                'room' => '# design-room',
                'roomStatus' => $localReady ? 'local' : 'draft',
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
            ],
            'design-lab' => [
                'slug' => 'design-lab',
                'label' => 'Design Lab',
                'meta' => 'Local',
                'prefix' => 'D',
                'summary' => 'A quieter workspace for shell structure, navigation studies, and UI direction.',
                'room' => '# shell-studies',
                'roomStatus' => 'draft',
                'participants' => [
                    ['label' => 'You', 'meta' => 'Human'],
                    ['label' => 'Visual Agent', 'meta' => 'Worker'],
                    ['label' => 'Layout Model', 'meta' => 'Model'],
                    ['label' => 'Critique Agent', 'meta' => 'Worker'],
                ],
                'notes' => [
                    'Reduce surface noise until the room can carry the experience on its own.',
                    'Treat navigation as durable structure, not as temporary marketing copy.',
                    'Let component work stay atomic so the shell can evolve without rewrites.',
                ],
                'tasks' => [
                    [
                        'label' => 'Tighten sidebar density',
                        'status' => 'Active',
                        'summary' => 'Remove truncation pressure and make the workspace selector feel native to the app frame.',
                    ],
                    [
                        'label' => 'Calm the center room',
                        'status' => 'Queued',
                        'summary' => 'Strip away explanatory blocks until the room reads clearly without helping text.',
                    ],
                ],
                'artifacts' => [
                    [
                        'label' => 'Sidebar studies',
                        'kind' => 'Mock',
                        'summary' => 'Current workspace selector and room list direction.',
                    ],
                    [
                        'label' => 'Type rhythm',
                        'kind' => 'Spec',
                        'summary' => 'Reduced tracking and line-height targets for the desktop shell.',
                    ],
                ],
                'decisions' => [
                    [
                        'label' => 'No marketing copy in-app',
                        'owner' => 'Design',
                        'summary' => 'The desktop shell should assume the user already chose to be here.',
                    ],
                ],
                'messages' => [
                    [
                        'speaker' => 'You',
                        'role' => 'Human',
                        'tone' => 'plain',
                        'meta' => 'Now',
                        'body' => 'Let the workspace selector behave like a selector, not like a section accordion.',
                    ],
                    [
                        'speaker' => 'Visual Agent',
                        'role' => 'Agent',
                        'tone' => 'accent',
                        'meta' => 'Draft',
                        'body' => 'Wider rails and calmer spacing are helping, but the shell still needs less explanation and more structure.',
                    ],
                ],
            ],
            'relay-cloud' => [
                'slug' => 'relay-cloud',
                'label' => 'Relay Cloud',
                'meta' => 'Remote',
                'prefix' => 'R',
                'summary' => 'A remote instance view for shared orchestration, worker presence, and linked team context.',
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
            ],
        ];
    }

    /**
     * @param  array<string, array{
     *     slug: string,
     *     label: string,
     *     meta: string,
     *     prefix: string
     * }>  $workspaces
     * @return array<int, array{label: string, meta?: string, active?: bool, muted?: bool, prefix: string, href: string}>
     */
    private function workspaceLinks(array $workspaces, string $activeWorkspace): array
    {
        return collect($workspaces)
            ->map(fn (array $workspace): array => [
                'label' => $workspace['label'],
                'meta' => $workspace['meta'],
                'active' => $workspace['slug'] === $activeWorkspace,
                'prefix' => $workspace['prefix'],
                'href' => route('home', ['workspace' => $workspace['slug']]),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string}>
     */
    private function favoriteLinks(string $activeRoom): array
    {
        return [
            ['label' => $activeRoom, 'active' => true, 'prefix' => '#', 'tone' => 'room'],
            ['label' => 'Derek Bourgeois', 'prefix' => 'D', 'tone' => 'human'],
            ['label' => 'Planner Agent', 'prefix' => '@', 'tone' => 'bot'],
        ];
    }

    /**
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string}>
     */
    private function roomLinks(string $activeRoom): array
    {
        return [
            ['label' => $activeRoom, 'active' => true, 'prefix' => '#', 'tone' => 'room'],
            ['label' => '# product-direction', 'prefix' => '#', 'tone' => 'room'],
            ['label' => 'Founders', 'prefix' => 'F', 'tone' => 'human'],
        ];
    }

    /**
     * @return array<int, array{label: string, active?: bool, prefix: string, tone: string}>
     */
    private function chatLinks(): array
    {
        return [
            ['label' => 'Derek Bourgeois', 'prefix' => 'D', 'tone' => 'human'],
            ['label' => 'Planner Agent', 'prefix' => '@', 'tone' => 'bot'],
            ['label' => 'Research Model', 'prefix' => '@', 'tone' => 'bot'],
        ];
    }

    /**
     * @return array<int, array{
     *     label: string,
     *     value: string,
     *     prefix: string,
     *     tone: string,
     *     subtitle: string
     * }>
     */
    private function chatContacts(): array
    {
        return [
            [
                'label' => 'Derek Bourgeois',
                'value' => 'derek-bourgeois',
                'prefix' => 'D',
                'tone' => 'human',
                'subtitle' => 'Human',
            ],
            [
                'label' => 'Planner Agent',
                'value' => 'planner-agent',
                'prefix' => '@',
                'tone' => 'bot',
                'subtitle' => 'Agent',
            ],
            [
                'label' => 'Research Model',
                'value' => 'research-model',
                'prefix' => '@',
                'tone' => 'bot',
                'subtitle' => 'Model',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private function workspaceTargets(): array
    {
        return [
            ['label' => 'Katra Local', 'value' => 'local'],
            ['label' => 'Relay Cloud', 'value' => 'relay-cloud'],
        ];
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

    public function __invoke(Request $request, SurrealRuntimeManager $runtimeManager): View
    {
        $workspace = null;
        $localReady = false;
        $desktopUiStates = DesktopUi::states();
        $mvpShellEnabled = DesktopUi::enabled($desktopUiStates, MvpShell::class);
        $workspaceNavigationEnabled = DesktopUi::enabled($desktopUiStates, WorkspaceNavigation::class);
        $conversationChannelsEnabled = DesktopUi::enabled($desktopUiStates, ConversationChannels::class);
        $taskSurfacesEnabled = DesktopUi::enabled($desktopUiStates, TaskSurfaces::class);
        $artifactSurfacesEnabled = DesktopUi::enabled($desktopUiStates, ArtifactSurfaces::class);
        $agentPresenceEnabled = DesktopUi::enabled($desktopUiStates, AgentPresence::class);

        try {
            if ($runtimeManager->ensureReady()) {
                $workspace = Workspace::desktopPreview();
                $localReady = true;
            }
        } catch (Throwable $exception) {
            if (! $exception instanceof RuntimeException) {
                report($exception);
            }
        }

        $workspaces = $this->workspaces($workspace, $localReady);
        $selectedWorkspace = $request->string('workspace')->value();
        $activeWorkspace = array_key_exists($selectedWorkspace, $workspaces) ? $selectedWorkspace : 'katra-local';
        $activeWorkspaceState = $workspaces[$activeWorkspace];

        return view('welcome', [
            'mvpShellEnabled' => $mvpShellEnabled,
            'workspaceNavigationEnabled' => $workspaceNavigationEnabled,
            'conversationChannelsEnabled' => $conversationChannelsEnabled,
            'taskSurfacesEnabled' => $taskSurfacesEnabled,
            'artifactSurfacesEnabled' => $artifactSurfacesEnabled,
            'agentPresenceEnabled' => $agentPresenceEnabled,
            'workspace' => $workspace,
            'previewState' => $activeWorkspaceState['roomStatus'],
            'activeWorkspace' => $activeWorkspaceState,
            'workspaceLinks' => $this->workspaceLinks($workspaces, $activeWorkspace),
            'workspaceTargets' => $this->workspaceTargets(),
            'favoriteLinks' => $this->favoriteLinks($activeWorkspaceState['room']),
            'roomLinks' => $this->roomLinks($activeWorkspaceState['room']),
            'chatLinks' => $this->chatLinks(),
            'chatContacts' => $this->chatContacts(),
            'conversationNodeTabs' => $this->conversationNodeTabs($activeWorkspaceState),
            'messages' => $activeWorkspaceState['messages'],
            'linkedTasks' => $activeWorkspaceState['tasks'],
            'linkedArtifacts' => $activeWorkspaceState['artifacts'],
            'decisions' => $activeWorkspaceState['decisions'],
            'feedbackGoals' => $activeWorkspaceState['notes'],
            'participants' => $activeWorkspaceState['participants'],
        ]);
    }
}
