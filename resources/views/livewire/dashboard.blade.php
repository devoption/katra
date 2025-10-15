<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-nord0 dark:text-nord6">Dashboard</h1>
        <p class="mt-2 text-nord3 dark:text-nord4">Welcome to Katra AI Workflow Engine</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Workflows -->
        <div class="bg-nord5 dark:bg-nord1 rounded-xl p-6 border border-nord4 dark:border-nord2 transition-all duration-200 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-nord3 dark:text-nord4">Active Workflows</p>
                    <p class="mt-2 text-3xl font-bold text-nord0 dark:text-nord6">0</p>
                </div>
                <div class="w-12 h-12 bg-nord8 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-nord8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Agents -->
        <div class="bg-nord5 dark:bg-nord1 rounded-xl p-6 border border-nord4 dark:border-nord2 transition-all duration-200 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-nord3 dark:text-nord4">Total Agents</p>
                    <p class="mt-2 text-3xl font-bold text-nord0 dark:text-nord6">{{ $stats['total_agents'] }}</p>
                </div>
                <div class="w-12 h-12 bg-nord9 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-nord9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Executions Today -->
        <div class="bg-nord5 dark:bg-nord1 rounded-xl p-6 border border-nord4 dark:border-nord2 transition-all duration-200 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-nord3 dark:text-nord4">Executions Today</p>
                    <p class="mt-2 text-3xl font-bold text-nord0 dark:text-nord6">{{ $stats['executions_today'] }}</p>
                </div>
                <div class="w-12 h-12 bg-nord14 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-nord14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="bg-nord5 dark:bg-nord1 rounded-xl p-6 border border-nord4 dark:border-nord2 transition-all duration-200 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-nord3 dark:text-nord4">Success Rate</p>
                    <p class="mt-2 text-3xl font-bold text-nord0 dark:text-nord6">{{ $stats['success_rate'] ?? '—' }}</p>
                </div>
                <div class="w-12 h-12 bg-nord15 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-nord15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Executions -->
        <div class="bg-nord5 dark:bg-nord1 rounded-xl border border-nord4 dark:border-nord2 overflow-hidden transition-colors duration-200">
            <div class="p-6 border-b border-nord4 dark:border-nord2">
                <h2 class="text-lg font-semibold text-nord0 dark:text-nord6">Recent Executions</h2>
            </div>
            <div class="p-6">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="mt-4 text-sm text-nord3 dark:text-nord4">No recent executions</p>
                </div>
            </div>
        </div>

        <!-- Quick Start -->
        <div class="bg-nord5 dark:bg-nord1 rounded-xl border border-nord4 dark:border-nord2 overflow-hidden transition-colors duration-200">
            <div class="p-6 border-b border-nord4 dark:border-nord2">
                <h2 class="text-lg font-semibold text-nord0 dark:text-nord6">Quick Start</h2>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('agents.create') }}" wire:navigate class="flex items-center justify-between p-4 rounded-lg border border-nord4 dark:border-nord2 hover:bg-nord4 dark:hover:bg-nord2 transition-all duration-200 group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-nord8 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-nord8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-nord0 dark:text-nord6">Create New Agent</span>
                    </div>
                    <svg class="w-5 h-5 text-nord3 dark:text-nord4 group-hover:text-nord8 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('workflows.create') }}" wire:navigate class="flex items-center justify-between p-4 rounded-lg border border-nord4 dark:border-nord2 hover:bg-nord4 dark:hover:bg-nord2 transition-all duration-200 group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-nord9 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-nord9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-nord0 dark:text-nord6">Create New Workflow</span>
                    </div>
                    <svg class="w-5 h-5 text-nord3 dark:text-nord4 group-hover:text-nord9 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="#" class="flex items-center justify-between p-4 rounded-lg border border-nord4 dark:border-nord2 hover:bg-nord4 dark:hover:bg-nord2 transition-all duration-200 group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-nord14 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-nord14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-nord0 dark:text-nord6">Chat with Katra</span>
                    </div>
                    <svg class="w-5 h-5 text-nord3 dark:text-nord4 group-hover:text-nord14 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
