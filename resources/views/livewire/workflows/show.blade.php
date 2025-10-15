<div x-data="{
        subscriptions: [],
        subscribeToExecution(executionId) {
            if (!this.subscriptions.includes(executionId)) {
                console.log('✅ Subscribing to execution:', executionId);
                console.log('🔌 Connection state BEFORE subscribe:', Echo.connector.pusher.connection.state);
                
                let channel = Echo.private('workflow-executions.' + executionId);
                console.log('📡 Channel object:', channel);
                
                channel.listen('WorkflowExecutionUpdated', (e) => {
                        console.log('🎉 Received update for execution:', e);
                        $wire.dispatch('workflow-execution-updated');
                    })
                    .error((error) => {
                        console.error('❌ Channel subscription error:', error);
                    });
                
                // Log connection state changes
                Echo.connector.pusher.connection.bind('state_change', (states) => {
                    console.log('📶 Connection state changed:', states.previous, '→', states.current);
                    if (states.current === 'failed' || states.current === 'unavailable') {
                        console.error('💥 Connection failed! Check Reverb server and config.');
                    }
                });
                
                this.subscriptions.push(executionId);
                console.log('📋 Current subscriptions:', this.subscriptions);
                console.log('🔌 Connection state AFTER subscribe:', Echo.connector.pusher.connection.state);
            } else {
                console.log('⏭️  Already subscribed to execution:', executionId);
            }
        }
     }"
     x-init="
        console.log('🚀 Initializing WebSocket subscriptions...');
        console.log('🔌 Echo status:', typeof Echo !== 'undefined' ? 'Ready' : 'Not loaded');
        
        @if($executions->isNotEmpty())
            @foreach($executions as $execution)
                @if($execution->status === 'running' || $execution->status === 'pending')
                    subscribeToExecution('{{ $execution->id }}');
                @endif
            @endforeach
        @endif
        
        Livewire.on('workflowTriggered', (data) => {
            if (data.executionId) {
                subscribeToExecution(data.executionId);
            }
        });
     "
>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('workflows.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Workflows</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>{{ $workflow->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">{{ $workflow->name }}</h1>
                @if($workflow->description)
                    <p class="mt-1 text-sm text-nord3 dark:text-nord4">{{ $workflow->description }}</p>
                @endif
            </div>
            <div class="flex gap-3">
                @if($workflow->is_active)
                    <x-ui.button wire:click="triggerWorkflow" variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Run Workflow
                    </x-ui.button>
                @endif
                <x-ui.button href="{{ route('workflows.edit', $workflow) }}" wire:navigate variant="outline">
                    Edit
                </x-ui.button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <!-- Stats Cards -->
        <x-ui.card>
            <div class="text-sm text-nord3 dark:text-nord4">Total Runs</div>
            <div class="text-2xl font-bold text-nord0 dark:text-nord6 mt-1">{{ $workflow->executions()->count() }}</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-nord3 dark:text-nord4">Success Rate</div>
            <div class="text-2xl font-bold text-nord0 dark:text-nord6 mt-1">
                @php
                    $total = $workflow->executions()->count();
                    $successful = $workflow->executions()->where('status', 'completed')->count();
                    $rate = $total > 0 ? round(($successful / $total) * 100) : 0;
                @endphp
                {{ $rate }}%
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-nord3 dark:text-nord4">Execution Mode</div>
            <div class="mt-1">
                <x-ui.badge variant="primary" size="sm">{{ ucfirst($workflow->execution_mode) }}</x-ui.badge>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-nord3 dark:text-nord4">Status</div>
            <div class="mt-1">
                @if($workflow->is_active)
                    <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                @else
                    <x-ui.badge variant="default" size="sm">Inactive</x-ui.badge>
                @endif
            </div>
        </x-ui.card>
    </div>

    <!-- Execution History -->
    <x-ui.card title="Execution History" :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Execution ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Triggered By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($executions as $execution)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-6 py-4">
                                <code class="text-xs text-nord0 dark:text-nord6">{{ Str::limit($execution->uuid, 8, '') }}</code>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusVariants = [
                                        'pending' => 'default',
                                        'running' => 'primary',
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'cancelled' => 'warning',
                                    ];
                                @endphp
                                <x-ui.badge :variant="$statusVariants[$execution->status] ?? 'default'" size="sm">
                                    {{ ucfirst($execution->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                {{ ucfirst($execution->triggered_by) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                {{ $execution->started_at?->diffForHumans() ?? 'Not started' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                @if($execution->started_at && $execution->completed_at)
                                    {{ $execution->started_at->diffInSeconds($execution->completed_at) }}s
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">No executions yet</p>
                                @if($workflow->is_active)
                                    <p class="mt-2">
                                        <x-ui.button wire:click="triggerWorkflow" variant="primary" size="sm">
                                            Run this workflow
                                        </x-ui.button>
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($executions->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $executions->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
