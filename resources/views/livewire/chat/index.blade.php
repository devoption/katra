<div
    class="flex h-[calc(100vh-4rem)] -m-6"
    x-data="{
        streamingMessageId: null,
        streamingContent: '',
        
        subscribeToConversation(conversationId) {
            if (conversationId) {
                Echo.private('conversations.' + conversationId)
                    .listen('.message.streaming', (e) => {
                        this.streamingMessageId = e.message_id;
                        
                        if (e.chunk) {
                            this.streamingContent += e.chunk;
                        }
                        
                        if (e.done) {
                            this.streamingMessageId = null;
                            this.streamingContent = '';
                            $wire.dispatch('message-streamed');
                        }
                    });
            }
        },
        
        scrollToBottom() {
            setTimeout(() => {
                const chatArea = document.getElementById('chat-messages');
                if (chatArea) {
                    chatArea.scrollTop = chatArea.scrollHeight;
                }
            }, 100);
        }
    }"
    x-init="
        @if($conversation)
            subscribeToConversation({{ $conversation->id }});
        @endif
        scrollToBottom();
        
        $watch('$wire.conversationId', (value) => {
            if (value) {
                subscribeToConversation(value);
            }
            scrollToBottom();
        });
    "
>
    <!-- Conversation Sidebar -->
    <div
        x-show="$wire.showSidebar"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="w-80 bg-nord6 dark:bg-nord0 border-r border-nord4 dark:border-nord1 flex flex-col"
    >
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-nord4 dark:border-nord1">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-nord0 dark:text-nord6">Conversations</h2>
                <button
                    wire:click="newConversation"
                    class="p-2 hover:bg-nord4 dark:hover:bg-nord1 rounded-lg transition-colors"
                    title="New Conversation"
                >
                    <svg class="w-5 h-5 text-nord0 dark:text-nord6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $group => $groupConversations)
                <div class="px-4 py-2">
                    <h3 class="text-xs font-semibold text-nord3 dark:text-nord4 uppercase tracking-wider mb-2">
                        {{ $group }}
                    </h3>
                    @foreach($groupConversations as $conv)
                        <button
                            wire:click="loadConversation({{ $conv->id }})"
                            class="w-full text-left p-3 rounded-lg mb-1 transition-colors {{ $conversationId === $conv->id ? 'bg-nord8 text-white' : 'hover:bg-nord4 dark:hover:bg-nord1 text-nord0 dark:text-nord6' }}"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate">
                                        {{ $conv->title ?? 'New Conversation' }}
                                    </div>
                                    <div class="text-xs opacity-75 mt-1">
                                        {{ $conv->agent->name }} • {{ $conv->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                                @if($conversationId === $conv->id)
                                    <button
                                        wire:click.stop="deleteConversation({{ $conv->id }})"
                                        wire:confirm="Are you sure you want to delete this conversation?"
                                        class="ml-2 p-1 hover:bg-nord11 hover:bg-opacity-20 rounded"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @empty
                <div class="p-4 text-center text-nord3 dark:text-nord4">
                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-sm">No conversations yet</p>
                    <p class="text-xs mt-1">Start chatting below!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-white dark:bg-nord1">
        <!-- Chat Header -->
        <div class="px-6 py-4 border-b border-nord4 dark:border-nord2 bg-nord6 dark:bg-nord0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button
                        wire:click="toggleSidebar"
                        class="p-2 hover:bg-nord4 dark:hover:bg-nord1 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 text-nord0 dark:text-nord6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-nord8 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-semibold">{{ substr($agent?->name ?? 'K', 0, 1) }}</span>
                        </div>
                        <div>
                            <div class="font-semibold text-nord0 dark:text-nord6">{{ $agent?->name ?? 'Katra' }}</div>
                            <div class="text-xs text-nord3 dark:text-nord4">{{ $agent?->role ?? 'Executive Assistant' }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Agent Selector -->
                    <select
                        wire:model.live="agentId"
                        class="px-3 py-1.5 text-sm rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                    >
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}@if($a->is_default) ⭐@endif</option>
                        @endforeach
                    </select>

                    <!-- New Conversation -->
                    <button
                        wire:click="newConversation"
                        class="px-4 py-1.5 text-sm bg-nord8 text-white rounded-lg hover:bg-nord9 transition-colors"
                    >
                        New Chat
                    </button>
                </div>
            </div>

            <!-- Agent Info (Collapsible) -->
            @if($agent)
                <div x-data="{ showInfo: false }" class="mt-3">
                    <button
                        @click="showInfo = !showInfo"
                        class="text-xs text-nord3 dark:text-nord4 hover:text-nord8 flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" :class="showInfo ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        Agent Details
                    </button>

                    <div x-show="showInfo" x-collapse class="mt-2 p-3 bg-nord4 dark:bg-nord2 rounded-lg text-sm">
                        <div class="space-y-2">
                            <div>
                                <span class="text-nord3 dark:text-nord4">Model:</span>
                                <span class="text-nord0 dark:text-nord6 ml-2">{{ $agent->model_provider }} / {{ $agent->model_name }}</span>
                            </div>
                            @if($agent->tools && $agent->tools->count() > 0)
                                <div>
                                    <span class="text-nord3 dark:text-nord4">Tools:</span>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($agent->tools as $tool)
                                            <span class="px-2 py-0.5 bg-nord8 bg-opacity-20 text-nord8 rounded text-xs">{{ $tool->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if($agent->context)
<div>
                                    <span class="text-nord3 dark:text-nord4">Context:</span>
                                    <span class="text-nord0 dark:text-nord6 ml-2">{{ $agent->context->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Messages Area -->
        <div
            id="chat-messages"
            class="flex-1 overflow-y-auto px-6 py-4 space-y-4"
            x-on:message-added="scrollToBottom()"
        >
            @if($conversation)
                @foreach($conversation->messages as $msg)
                    @if($msg->isUser())
                        <!-- User Message -->
                        <div class="flex justify-end">
                            <div class="max-w-3xl">
                                <div class="flex items-end gap-2 justify-end mb-1">
                                    <span class="text-xs text-nord3 dark:text-nord4">{{ $msg->created_at->format('H:i') }}</span>
                                    <span class="text-sm font-medium text-nord0 dark:text-nord6">You</span>
                                </div>
                                <div class="bg-nord8 text-white rounded-2xl rounded-tr-sm px-4 py-3">
                                    <div class="prose prose-sm max-w-none text-white">
                                        {!! Str::markdown($msg->content) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($msg->isAssistant())
                        <!-- Assistant Message -->
                        <div class="flex justify-start">
                            <div class="max-w-3xl">
                                <div class="flex items-end gap-2 mb-1">
                                    <div class="w-6 h-6 bg-nord14 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-semibold">{{ substr($msg->agent?->name ?? 'K', 0, 1) }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-nord0 dark:text-nord6">{{ $msg->agent?->name ?? 'Katra' }}</span>
                                    <span class="text-xs text-nord3 dark:text-nord4">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                                
                                <!-- Tool Calls (if any) -->
                                @if($msg->hasToolCalls())
                                    <div class="mb-2 space-y-1">
                                        @foreach($msg->tool_calls as $toolCall)
                                            <div x-data="{ expanded: false }" class="bg-nord13 bg-opacity-10 border border-nord13 rounded-lg p-2">
                                                <button
                                                    @click="expanded = !expanded"
                                                    class="flex items-center justify-between w-full text-sm"
                                                >
                                                    <div class="flex items-center gap-2 text-nord13">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <span>{{ $toolCall['function']['name'] ?? 'Tool Call' }}</span>
                                                    </div>
                                                    <svg class="w-4 h-4 text-nord3 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                                
                                                <div x-show="expanded" x-collapse class="mt-2 text-xs">
                                                    <pre class="bg-nord0 dark:bg-nord3 text-nord6 dark:text-nord6 p-2 rounded overflow-x-auto">{{ json_encode($toolCall['function']['arguments'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Assistant Response -->
                                <div class="bg-nord4 dark:bg-nord2 text-nord0 dark:text-nord6 rounded-2xl rounded-tl-sm px-4 py-3">
                                    <div class="prose prose-sm max-w-none dark:prose-invert">
                                        <template x-if="streamingMessageId === {{ $msg->id }}">
                                            <div x-html="marked.parse(streamingContent || '...')" class="markdown-content"></div>
                                        </template>
                                        <template x-if="streamingMessageId !== {{ $msg->id }}">
                                            <div class="markdown-content">{!! Str::markdown($msg->content ?: '...') !!}</div>
                                        </template>
                                    </div>

                                    @if($msg->is_streaming && !$msg->is_complete)
                                        <div class="flex items-center gap-1 mt-2 text-nord3 dark:text-nord4">
                                            <div class="w-2 h-2 bg-nord8 rounded-full animate-pulse"></div>
                                            <div class="w-2 h-2 bg-nord8 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                                            <div class="w-2 h-2 bg-nord8 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Tool Results (if any) -->
                                @if($msg->hasToolResults())
                                    <div class="mt-2 space-y-1">
                                        @foreach($msg->tool_results as $result)
                                            <div class="bg-nord14 bg-opacity-10 border border-nord14 rounded-lg p-2 text-xs">
                                                <div class="flex items-center gap-2 text-nord14">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>{{ $result['tool'] ?? 'Tool' }} completed</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <!-- Welcome Message -->
                <div class="flex items-center justify-center h-full">
                    <div class="text-center max-w-2xl px-4">
                        <div class="w-20 h-20 bg-nord8 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white text-3xl font-bold">K</span>
                        </div>
                        <h1 class="text-3xl font-bold text-nord0 dark:text-nord6 mb-2">Chat with Katra</h1>
                        <p class="text-nord3 dark:text-nord4 mb-6">
                            Your intelligent executive assistant powered by AI. Ask me anything, trigger workflows, or get help with your tasks.
                        </p>
                        
                        @if($agent && $agent->tools && $agent->tools->count() > 0)
                            <div class="text-left bg-nord4 dark:bg-nord2 rounded-lg p-4 mb-4">
                                <h3 class="text-sm font-semibold text-nord0 dark:text-nord6 mb-2">Available Tools:</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($agent->tools as $tool)
                                        <div class="px-3 py-1 bg-white dark:bg-nord1 border border-nord4 dark:border-nord3 rounded text-xs text-nord0 dark:text-nord6">
                                            {{ $tool->name }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="p-3 bg-nord10 bg-opacity-10 rounded-lg text-left">
                                <div class="font-medium text-nord10 mb-1">💬 Natural Conversations</div>
                                <p class="text-xs text-nord3 dark:text-nord4">Chat naturally and get intelligent responses</p>
                            </div>
                            <div class="p-3 bg-nord14 bg-opacity-10 rounded-lg text-left">
                                <div class="font-medium text-nord14 mb-1">🔧 Tool Usage</div>
                                <p class="text-xs text-nord3 dark:text-nord4">I can use tools to help accomplish tasks</p>
                            </div>
                            <div class="p-3 bg-nord13 bg-opacity-10 rounded-lg text-left">
                                <div class="font-medium text-nord13 mb-1">🔄 Workflow Triggers</div>
                                <p class="text-xs text-nord3 dark:text-nord4">Trigger and manage your workflows</p>
                            </div>
                            <div class="p-3 bg-nord15 bg-opacity-10 rounded-lg text-left">
                                <div class="font-medium text-nord15 mb-1">🧠 Context Aware</div>
                                <p class="text-xs text-nord3 dark:text-nord4">I remember our conversation history</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Message Input -->
        <div class="p-6 border-t border-nord4 dark:border-nord2 bg-nord6 dark:bg-nord0">
            <form wire:submit="sendMessage" class="flex items-end gap-3">
                <div class="flex-1">
                    <textarea
                        wire:model="message"
                        placeholder="Type your message..."
                        rows="1"
                        class="w-full px-4 py-3 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors resize-none"
                        x-data="{
                            resize() {
                                $el.style.height = 'auto';
                                $el.style.height = Math.min($el.scrollHeight, 200) + 'px';
                            }
                        }"
                        x-on:input="resize()"
                        x-init="resize()"
                        @keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); $el.style.height = 'auto'; }"
                        @if($isSending) disabled @endif
                    ></textarea>
                    <p class="text-xs text-nord3 dark:text-nord4 mt-1">
                        Press Enter to send, Shift+Enter for new line
                    </p>
                </div>

                <button
                    type="submit"
                    @if($isSending || empty(trim($message))) disabled @endif
                    class="px-6 py-3 bg-nord8 text-white rounded-lg hover:bg-nord9 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    @if($isSending)
                        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    @endif
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
@endpush
