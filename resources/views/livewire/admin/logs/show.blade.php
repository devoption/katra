<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('admin.logs.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Logs</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Interaction Details</span>
        </div>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">AI Interaction</h1>
            <div class="flex items-center gap-2">
                <button
                    wire:click="toggleTraining"
                    class="px-4 py-2 rounded-lg {{ $interaction->include_in_training ? 'bg-nord11 text-white' : 'bg-nord14 text-white' }} hover:opacity-90 transition-opacity"
                >
                    {{ $interaction->include_in_training ? '🚫 Exclude from Training' : '✅ Include in Training' }}
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Interaction Details -->
            <x-ui.card title="Interaction Details">
                <div class="space-y-4">
                    <!-- Type & Status -->
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-nord3 dark:text-nord4">Type:</span>
                            <x-ui.badge :variant="match($interaction->type) {
                                'chat' => 'primary',
                                'workflow_execution' => 'success',
                                'agent_step' => 'default',
                                'tool_execution' => 'warning',
                                default => 'default'
                            }" size="sm" class="ml-2">
                                {{ ucfirst(str_replace('_', ' ', $interaction->type)) }}
                            </x-ui.badge>
                        </div>
                        <div>
                            <span class="text-sm text-nord3 dark:text-nord4">Status:</span>
                            <x-ui.badge :variant="$interaction->status === 'success' ? 'success' : 'danger'" size="sm" class="ml-2">
                                {{ ucfirst($interaction->status) }}
                            </x-ui.badge>
                        </div>
                    </div>

                    <!-- Model Info -->
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-nord4 dark:border-nord3">
                        <div>
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">Provider</span>
                            <p class="text-sm text-nord3 dark:text-nord4">{{ $interaction->model_provider ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">Model</span>
                            <p class="text-sm text-nord3 dark:text-nord4">{{ $interaction->model_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">Temperature</span>
                            <p class="text-sm text-nord3 dark:text-nord4">{{ $interaction->temperature ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">Max Tokens</span>
                            <p class="text-sm text-nord3 dark:text-nord4">{{ $interaction->max_tokens ? number_format($interaction->max_tokens) : 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- System Prompt -->
                    @if($interaction->system_prompt)
                        <div class="pt-4 border-t border-nord4 dark:border-nord3">
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">System Prompt</span>
                            <div class="mt-2 p-3 bg-nord4 dark:bg-nord2 rounded-lg">
                                <pre class="text-sm text-nord0 dark:text-nord6 whitespace-pre-wrap">{{ $interaction->system_prompt }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- User Prompt -->
                    <div class="pt-4 border-t border-nord4 dark:border-nord3">
                        <span class="text-sm font-medium text-nord0 dark:text-nord6">User Prompt</span>
                        <div class="mt-2 p-3 bg-nord10 bg-opacity-10 dark:bg-opacity-20 rounded-lg border-l-4 border-nord10">
                            <pre class="text-sm text-nord0 dark:text-nord6 whitespace-pre-wrap">{{ $interaction->prompt }}</pre>
                        </div>
                    </div>

                    <!-- AI Response -->
                    @if($interaction->response)
                        <div class="pt-4 border-t border-nord4 dark:border-nord3">
                            <span class="text-sm font-medium text-nord0 dark:text-nord6">AI Response</span>
                            <div class="mt-2 p-3 bg-nord14 bg-opacity-10 dark:bg-opacity-20 rounded-lg border-l-4 border-nord14">
                                <pre class="text-sm text-nord0 dark:text-nord6 whitespace-pre-wrap">{{ $interaction->response }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if($interaction->error_message)
                        <div class="pt-4 border-t border-nord4 dark:border-nord3">
                            <span class="text-sm font-medium text-nord11">Error Message</span>
                            <div class="mt-2 p-3 bg-nord11 bg-opacity-10 dark:bg-opacity-20 rounded-lg border-l-4 border-nord11">
                                <pre class="text-sm text-nord11 whitespace-pre-wrap">{{ $interaction->error_message }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Existing Feedback -->
            @if($interaction->feedback->count() > 0)
                <x-ui.card title="Feedback History ({{ $interaction->feedback->count() }})">
                    <div class="space-y-4">
                        @foreach($interaction->feedback as $feedback)
                            <div class="p-4 bg-nord4 dark:bg-nord2 rounded-lg">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-nord0 dark:text-nord6">{{ $feedback->user->full_name }}</span>
                                        @if($feedback->rating)
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="{{ $i <= $feedback->rating ? 'text-yellow-400' : 'text-nord3' }}">★</span>
                                                @endfor
                                            </div>
                                        @elseif($feedback->thumbs_up !== null)
                                            <span class="text-lg">{{ $feedback->thumbs_up ? '👍' : '👎' }}</span>
                                        @endif
                                        @if($feedback->verified_by_admin)
                                            <x-ui.badge variant="success" size="sm">✓ Verified</x-ui.badge>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if(!$feedback->verified_by_admin && auth()->user()->isAdmin())
                                            <button
                                                wire:click="verifyFeedback({{ $feedback->id }})"
                                                class="text-sm text-nord14 hover:text-nord13"
                                                title="Verify Feedback"
                                            >
                                                Verify
                                            </button>
                                        @endif
                                        <button
                                            wire:click="deleteFeedback({{ $feedback->id }})"
                                            wire:confirm="Are you sure you want to delete this feedback?"
                                            class="text-sm text-nord11 hover:text-nord12"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                @if($feedback->feedback_type)
                                    <x-ui.badge variant="default" size="sm" class="mb-2">{{ ucfirst(str_replace('_', ' ', $feedback->feedback_type)) }}</x-ui.badge>
                                @endif

                                @if($feedback->correction_text)
                                    <div class="mt-2">
                                        <span class="text-xs font-medium text-nord0 dark:text-nord6">Correction:</span>
                                        <p class="text-sm text-nord3 dark:text-nord4 mt-1">{{ $feedback->correction_text }}</p>
                                    </div>
                                @endif

                                @if($feedback->explanation)
                                    <div class="mt-2">
                                        <span class="text-xs font-medium text-nord0 dark:text-nord6">Explanation:</span>
                                        <p class="text-sm text-nord3 dark:text-nord4 mt-1">{{ $feedback->explanation }}</p>
                                    </div>
                                @endif

                                @if($feedback->tags && count($feedback->tags) > 0)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($feedback->tags as $tag)
                                            <span class="px-2 py-1 text-xs bg-nord8 bg-opacity-20 text-nord8 rounded">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-2 text-xs text-nord3 dark:text-nord4">
                                    Weight: {{ $feedback->weight }} | {{ $feedback->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif

            <!-- Add Feedback Form -->
            <x-ui.card title="📝 Add Training Feedback">
                <form wire:submit="submitFeedback" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Rating -->
                        <div>
                            <label for="rating" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                                Rating (1-5 stars)
                            </label>
                            <select
                                id="rating"
                                wire:model="rating"
                                class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                            >
                                <option value="">Select rating</option>
                                <option value="1">1 - Poor</option>
                                <option value="2">2 - Fair</option>
                                <option value="3">3 - Good</option>
                                <option value="4">4 - Very Good</option>
                                <option value="5">5 - Excellent</option>
                            </select>
                        </div>

                        <!-- Thumbs Up/Down -->
                        <div>
                            <label class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                                Quick Feedback
                            </label>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    wire:click="$set('thumbs_up', true)"
                                    class="flex-1 px-4 py-2 rounded-lg border {{ $thumbs_up === true ? 'bg-nord14 text-white border-nord14' : 'border-nord4 dark:border-nord3 text-nord0 dark:text-nord6' }} hover:opacity-75 transition-opacity"
                                >
                                    👍 Helpful
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('thumbs_up', false)"
                                    class="flex-1 px-4 py-2 rounded-lg border {{ $thumbs_up === false ? 'bg-nord11 text-white border-nord11' : 'border-nord4 dark:border-nord3 text-nord0 dark:text-nord6' }} hover:opacity-75 transition-opacity"
                                >
                                    👎 Not Helpful
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Type -->
                    <div>
                        <label for="feedback_type" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                            Feedback Type
                        </label>
                        <select
                            id="feedback_type"
                            wire:model="feedback_type"
                            class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                        >
                            <option value="">Select type</option>
                            <option value="helpful">Helpful</option>
                            <option value="unhelpful">Unhelpful</option>
                            <option value="incorrect">Incorrect</option>
                            <option value="offensive">Offensive</option>
                            <option value="brilliant">Brilliant</option>
                            <option value="needs_improvement">Needs Improvement</option>
                        </select>
                    </div>

                    <!-- Correction Text -->
                    <div>
                        <label for="correction_text" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                            🎯 Correction (What should the AI have said?)
                        </label>
                        <textarea
                            id="correction_text"
                            wire:model="correction_text"
                            rows="3"
                            placeholder="Provide the correct response for training..."
                            class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                        ></textarea>
                        <p class="mt-1 text-xs text-nord3 dark:text-nord4">This is critical for model fine-tuning!</p>
                    </div>

                    <!-- Explanation -->
                    <div>
                        <label for="explanation" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                            Explanation (Why?)
                        </label>
                        <textarea
                            id="explanation"
                            wire:model="explanation"
                            rows="2"
                            placeholder="Explain why this correction is needed..."
                            class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                        ></textarea>
                    </div>

                    <!-- Weight -->
                    <div>
                        <label for="weight" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                            Feedback Weight (0.0 - 1.0)
                        </label>
                        <input
                            type="number"
                            id="weight"
                            wire:model="weight"
                            step="0.01"
                            min="0"
                            max="1"
                            class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                        >
                        <p class="mt-1 text-xs text-nord3 dark:text-nord4">Higher weight = more important for training (1.0 = maximum importance)</p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                            Admin Notes
                        </label>
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="2"
                            placeholder="Any additional notes..."
                            class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                        ></textarea>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="primary">
                            Submit Feedback
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Metadata -->
            <x-ui.card title="Metadata">
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-nord3 dark:text-nord4">UUID:</span>
                        <span class="block font-mono text-xs text-nord0 dark:text-nord6 mt-1">{{ $interaction->uuid }}</span>
                    </div>
                    <div>
                        <span class="text-nord3 dark:text-nord4">Created:</span>
                        <span class="block text-nord0 dark:text-nord6 mt-1">{{ $interaction->created_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="text-nord3 dark:text-nord4">User:</span>
                        <span class="block text-nord0 dark:text-nord6 mt-1">{{ $interaction->user->full_name }}</span>
                    </div>
                    @if($interaction->agent)
                        <div>
                            <span class="text-nord3 dark:text-nord4">Agent:</span>
                            <span class="block text-nord0 dark:text-nord6 mt-1">{{ $interaction->agent->name }}</span>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Performance Metrics -->
            <x-ui.card title="Performance Metrics">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-nord3 dark:text-nord4">Prompt Tokens:</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ number_format($interaction->prompt_tokens ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-nord3 dark:text-nord4">Completion Tokens:</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ number_format($interaction->completion_tokens ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-nord4 dark:border-nord3 pt-3">
                        <span class="text-sm text-nord3 dark:text-nord4">Total Tokens:</span>
                        <span class="font-semibold text-nord8">{{ number_format($interaction->total_tokens ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-nord3 dark:text-nord4">Latency:</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $interaction->formatted_latency }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-nord3 dark:text-nord4">Cost:</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $interaction->formatted_cost }}</span>
                    </div>
                    @if($interaction->quality_score)
                        <div class="flex justify-between items-center border-t border-nord4 dark:border-nord3 pt-3">
                            <span class="text-sm text-nord3 dark:text-nord4">Quality Score:</span>
                            <span class="font-semibold text-nord14">{{ $interaction->quality_score }}</span>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Training Data Status -->
            <x-ui.card title="Training Data">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-nord3 dark:text-nord4">Included:</span>
                        <span class="font-semibold {{ $interaction->include_in_training ? 'text-nord14' : 'text-nord11' }}">
                            {{ $interaction->include_in_training ? '✅ Yes' : '❌ No' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-nord3 dark:text-nord4">Feedback Count:</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $interaction->feedback->count() }}</span>
                    </div>
                    @if($interaction->averageRating())
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-nord3 dark:text-nord4">Avg Rating:</span>
                            <span class="font-semibold text-nord0 dark:text-nord6">{{ number_format($interaction->averageRating(), 1) }} / 5</span>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
