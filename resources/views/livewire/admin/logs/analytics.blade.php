<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">AI Analytics</h1>
            <div class="flex items-center gap-2">
                <select
                    wire:model.live="period"
                    class="px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
                    <option value="week">Last 7 Days</option>
                    <option value="month">Last 30 Days</option>
                    <option value="all">All Time</option>
                </select>
                <x-ui.button wire:click="export" variant="primary">
                    📊 Export Training Data
                </x-ui.button>
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-ui.card :padding="true">
            <div class="text-3xl font-bold text-primary">{{ number_format($stats['total_interactions']) }}</div>
            <div class="text-sm text-nord3 dark:text-nord4 mt-1">Total Interactions</div>
            <div class="text-xs text-nord3 dark:text-nord4 mt-2">
                Success: {{ number_format($stats['successful']) }} | Failed: {{ number_format($stats['failed']) }}
            </div>
        </x-ui.card>

        <x-ui.card :padding="true">
            <div class="text-3xl font-bold text-nord14">{{ number_format($stats['total_tokens'] / 1000000, 2) }}M</div>
            <div class="text-sm text-nord3 dark:text-nord4 mt-1">Total Tokens</div>
            <div class="text-xs text-nord3 dark:text-nord4 mt-2">
                Avg: {{ number_format(($stats['total_interactions'] > 0 ? $stats['total_tokens'] / $stats['total_interactions'] : 0)) }} per interaction
            </div>
        </x-ui.card>

        <x-ui.card :padding="true">
            <div class="text-3xl font-bold text-nord11">${{ number_format($stats['total_cost'], 2) }}</div>
            <div class="text-sm text-nord3 dark:text-nord4 mt-1">Total Cost</div>
            <div class="text-xs text-nord3 dark:text-nord4 mt-2">
                Avg: ${{ number_format(($stats['total_interactions'] > 0 ? $stats['total_cost'] / $stats['total_interactions'] : 0), 4) }} per interaction
            </div>
        </x-ui.card>

        <x-ui.card :padding="true">
            <div class="text-3xl font-bold text-primary">{{ number_format($stats['avg_latency']) }}ms</div>
            <div class="text-sm text-nord3 dark:text-nord4 mt-1">Avg Latency</div>
            <div class="text-xs text-nord3 dark:text-nord4 mt-2">
                Training Data: {{ number_format($stats['training_data']) }}
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- By Provider -->
        <x-ui.card title="Usage by Provider">
            <div class="space-y-3">
                @forelse($byProvider as $provider)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">{{ ucfirst($provider->model_provider ?? 'Unknown') }}</span>
                            <span class="text-sm text-nord3 dark:text-nord4">{{ number_format($provider->count) }} calls</span>
                        </div>
                        <div class="w-full bg-nord4 dark:bg-nord2 rounded-full h-2">
                            <div
                                class="bg-primary h-2 rounded-full"
                                style="width: {{ ($stats['total_interactions'] > 0 ? ($provider->count / $stats['total_interactions']) * 100 : 0) }}%"
                            ></div>
                        </div>
                        <div class="flex justify-between text-xs text-nord3 dark:text-nord4 mt-1">
                            <span>Tokens: {{ number_format($provider->total_tokens) }}</span>
                            <span>Cost: ${{ number_format($provider->total_cost, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-nord3 dark:text-nord4">No data available</p>
                @endforelse
            </div>
        </x-ui.card>

        <!-- By Type -->
        <x-ui.card title="Usage by Type">
            <div class="space-y-3">
                @forelse($byType as $type)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">{{ ucfirst(str_replace('_', ' ', $type->type)) }}</span>
                            <span class="text-sm text-nord3 dark:text-nord4">{{ number_format($type->count) }}</span>
                        </div>
                        <div class="w-full bg-nord4 dark:bg-nord2 rounded-full h-2">
                            <div
                                class="bg-nord14 h-2 rounded-full"
                                style="width: {{ ($stats['total_interactions'] > 0 ? ($type->count / $stats['total_interactions']) * 100 : 0) }}%"
                            ></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-nord3 dark:text-nord4">No data available</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <!-- Daily Activity -->
    <x-ui.card title="Daily Activity" class="mb-6">
        <div class="h-64 flex items-end justify-between gap-2">
            @forelse($dailyActivity as $day)
                <div class="flex-1 flex flex-col items-center">
                    <div
                        class="w-full bg-primary rounded-t transition-all"
                        style="height: {{ $dailyActivity->max('count') > 0 ? ($day->count / $dailyActivity->max('count')) * 100 : 0 }}%"
                        title="{{ $day->count }} interactions on {{ $day->date }}"
                    ></div>
                    <span class="text-xs text-nord3 dark:text-nord4 mt-2">{{ \Carbon\Carbon::parse($day->date)->format('m/d') }}</span>
                </div>
            @empty
                <p class="text-sm text-nord3 dark:text-nord4">No activity data</p>
            @endforelse
        </div>
    </x-ui.card>

    <!-- Top Users -->
    <x-ui.card title="Top Users">
        <div class="space-y-2">
            @forelse($topUsers as $topUser)
                <div class="flex items-center justify-between py-2 border-b border-nord4 dark:border-nord3 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-semibold">
                            {{ substr($topUser->user->first_name, 0, 1) }}{{ substr($topUser->user->last_name, 0, 1) }}
                        </div>
                        <span class="text-sm font-medium text-nord0 dark:text-nord6">{{ $topUser->user->full_name }}</span>
                    </div>
                    <span class="text-sm text-nord3 dark:text-nord4">{{ number_format($topUser->count) }} interactions</span>
                </div>
            @empty
                <p class="text-sm text-nord3 dark:text-nord4">No user data available</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
