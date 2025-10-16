<?php

namespace App\Livewire\Chat;

use App\Models\Agent;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Chat with Katra')]
class Index extends Component
{
    public ?int $conversationId = null;

    public ?int $agentId = null;

    public string $message = '';

    public bool $showSidebar = true;

    public bool $isSending = false;

    public function mount($conversation = null): void
    {
        // Handle conversation parameter (can be ID or model)
        if ($conversation instanceof Conversation) {
            if ($conversation->user_id === auth()->id()) {
                $this->conversationId = $conversation->id;
                $this->agentId = $conversation->agent_id;
            }
        } elseif (is_numeric($conversation)) {
            $conv = Conversation::where('id', $conversation)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($conv) {
                $this->conversationId = $conv->id;
                $this->agentId = $conv->agent_id;
            }
        }

        // Default to Katra agent if no conversation
        if (!$this->agentId) {
            $defaultAgent = Agent::where('is_default', true)->first();
            $this->agentId = $defaultAgent?->id;
        }
    }

    public function getConversationProperty(): ?Conversation
    {
        if (! $this->conversationId) {
            return null;
        }

        return Conversation::with(['messages.agent', 'agent'])
            ->findOrFail($this->conversationId);
    }

    public function getAgentProperty(): ?Agent
    {
        if (! $this->agentId) {
            return null;
        }

        return Agent::with(['tools', 'context'])->findOrFail($this->agentId);
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->message))) {
            return;
        }

        $this->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $this->isSending = true;

        try {
            $chatService = app(ChatService::class);
            $agent = $this->agent;

            if (! $agent) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No agent selected. Please select an agent.',
                ]);

                return;
            }

            // Get or create conversation
            if (! $this->conversationId) {
                $conversation = Conversation::create([
                    'user_id' => auth()->id(),
                    'agent_id' => $agent->id,
                ]);

                $this->conversationId = $conversation->id;
                
                // Dispatch event to trigger frontend subscription
                $this->dispatch('conversationCreated', conversationId: $conversation->id);
            } else {
                $conversation = $this->conversation;
            }

            // Send message (this handles streaming via ChatService)
            $userMessage = $this->message;
            $this->message = '';

            // Dispatch to background with a small delay to ensure subscription is set up
            dispatch(function () use ($chatService, $conversation, $userMessage, $agent) {
                // Small delay to ensure frontend is subscribed
                usleep(500000); // 0.5 seconds
                $chatService->sendMessage($conversation, $userMessage, $agent);
            })->afterResponse();

        } catch (\Exception $e) {
            Log::error('Chat send error', ['error' => $e->getMessage()]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to send message. Please try again.',
            ]);
        } finally {
            $this->isSending = false;
        }
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->message = '';

        // Reset to default agent
        $defaultAgent = Agent::where('is_default', true)->first();
        $this->agentId = $defaultAgent?->id;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Started new conversation.',
        ]);
    }

    public function loadConversation(int $conversationId): void
    {
        $conversation = Conversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $conversation) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Conversation not found.',
            ]);

            return;
        }

        $this->conversationId = $conversation->id;
        $this->agentId = $conversation->agent_id;
    }

    public function switchAgent(int $agentId): void
    {
        $this->agentId = $agentId;

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Switched to '.Agent::find($agentId)?->name,
        ]);
    }

    public function deleteConversation(int $conversationId): void
    {
        $conversation = Conversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if ($conversation) {
            $conversation->delete();

            if ($this->conversationId === $conversationId) {
                $this->newConversation();
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Conversation deleted.',
            ]);
        }
    }

    public function toggleSidebar(): void
    {
        $this->showSidebar = ! $this->showSidebar;
    }

    #[On('message-streamed')]
    public function onMessageStreamed(): void
    {
        // Livewire will auto-refresh when this event is received
    }

    public function render()
    {
        $conversations = Conversation::where('user_id', auth()->id())
            ->with('agent')
            ->latest()
            ->limit(50)
            ->get()
            ->groupBy(function ($conversation) {
                $date = $conversation->created_at;

                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } elseif ($date->isCurrentWeek()) {
                    return 'This Week';
                } elseif ($date->isCurrentMonth()) {
                    return 'This Month';
                } else {
                    return $date->format('F Y');
                }
            });

        $agents = Agent::where('is_active', true)
            ->orderByRaw('is_default DESC')
            ->get();
   
        return view('livewire.chat.index', [
            'conversations' => $conversations,
            'agents' => $agents,
            'conversation' => $this->conversation,
            'agent' => $this->agent,
        ]);
    }
}
