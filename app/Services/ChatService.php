<?php

namespace App\Services;

use App\Events\ConversationMessageStreaming;
use App\Models\Agent;
use App\Models\AiInteraction;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatService
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Send a message and get streaming response
     */
    public function sendMessage(
        Conversation $conversation,
        string $userMessage,
        ?Agent $agent = null,
        bool $persistUserMessage = true
    ): ConversationMessage {
        $agent = $agent ?? $conversation->agent;

        if ($persistUserMessage) {
            // Create user message
            $conversation->messages()->create([
                'role' => 'user',
                'content' => $userMessage,
                'is_complete' => true,
            ]);

            // Auto-generate conversation title from first message
            if ($conversation->messages()->where('role', 'user')->count() === 1) {
                $conversation->generateTitle();
            }
        }

        // Create assistant message placeholder (for streaming)
        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'agent_id' => $agent->id,
            'content' => '',
            'is_streaming' => true,
            'is_complete' => false,
        ]);

        // Build conversation history for context
        $messages = $this->buildMessageHistory($conversation, $agent);

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Start AI interaction logging
        $aiInteraction = $this->createAiInteraction($agent, $userMessage, $conversation);

        // Stream response from Ollama
        $this->streamOllamaResponse(
            $agent,
            $messages,
            $assistantMessage,
            $aiInteraction
        );

        return $assistantMessage;
    }

    /**
     * Build message history with context
     */
    protected function buildMessageHistory(Conversation $conversation, Agent $agent): array
    {
        $messages = [];

        // Add system message with user context
        $user = $conversation->user;
        $systemPrompt = $agent->system_prompt;

        // Inject user context
        $systemPrompt .= "\n\nYou are speaking with {$user->full_name} (Email: {$user->email}).";

        // Add agent context if available
        if ($agent->context) {
            $systemPrompt .= "\n\nAdditional Context:\n".($agent->context->description ?? '');
        }

        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt,
        ];

        // Add conversation history (last 10 messages for context)
        $recentMessages = $conversation->messages()
            ->where('is_complete', true)
            ->latest()
            ->limit(10)
            ->get()
            ->reverse();

        foreach ($recentMessages as $message) {
            if ($message->role === 'user' || $message->role === 'assistant') {
                $messages[] = [
                    'role' => $message->role,
                    'content' => $message->content,
                ];
            }
        }

        return $messages;
    }

    /**
     * Stream response from Ollama
     */
    protected function streamOllamaResponse(
        Agent $agent,
        array $messages,
        ConversationMessage $assistantMessage,
        AiInteraction $aiInteraction
    ): void {
        $baseUrl = config('services.ollama.base_url');
        $fullResponse = '';
        $startTime = microtime(true);

        try {
            // Prepare tools for Ollama if agent has them
            $tools = $this->prepareToolsForOllama($agent);

            $requestData = [
                'model' => $agent->model_name,
                'messages' => $messages,
                'stream' => true,
                'options' => [
                    'temperature' => $agent->creativity_level,
                ],
            ];

            if (! empty($tools)) {
                $requestData['tools'] = $tools;
            }

            // Stream the response
            $response = Http::timeout(120)
                ->withOptions(['stream' => true])
                ->post("{$baseUrl}/api/chat", $requestData);

            $body = $response->getBody();
            $toolCalls = [];

            while (! $body->eof()) {
                $line = $body->read(8192);
                $chunks = explode("\n", $line);

                foreach ($chunks as $chunk) {
                    if (empty(trim($chunk))) {
                        continue;
                    }

                    $data = json_decode($chunk, true);

                    if (isset($data['message'])) {
                        $message = $data['message'];

                        // Handle content streaming
                        if (isset($message['content']) && !empty(trim($message['content']))) {
                            $fullResponse .= $message['content'];

                            // Broadcast chunk via Reverb
                            broadcast(new ConversationMessageStreaming(
                                $assistantMessage,
                                $message['content'],
                                false
                            ))->toOthers();
                        }

                        // Handle tool calls
                        if (isset($message['tool_calls'])) {
                            $toolCalls = array_merge($toolCalls, $message['tool_calls']);
                        }
                    }

                    // Check if done
                    if (isset($data['done']) && $data['done'] === true) {
                        break 2;
                    }
                }
            }

            $endTime = microtime(true);
            $latency = (int) (($endTime - $startTime) * 1000);

            // Update assistant message
            $assistantMessage->update([
                'content' => $fullResponse ?: 'I apologize, but I was unable to generate a response. Please try again.',
                'tool_calls' => ! empty($toolCalls) ? $toolCalls : null,
                'is_streaming' => false,
                'is_complete' => true,
                'metadata' => [
                    'model' => $agent->model_name,
                    'provider' => $agent->model_provider,
                    'latency_ms' => $latency,
                ],
            ]);

            // Update AI interaction log
            $aiInteraction->update([
                'status' => 'success',
                'response' => $fullResponse,
                'tool_calls' => $toolCalls,
                'latency_ms' => $latency,
            ]);

            // Handle tool calls if present
            if (! empty($toolCalls)) {
                $this->executeToolCalls($toolCalls, $assistantMessage, $agent);
            }

            // Final broadcast
            broadcast(new ConversationMessageStreaming(
                $assistantMessage,
                '',
                true
            ))->toOthers();

        } catch (\Exception $e) {
            Log::error('Chat streaming error', [
                'error' => $e->getMessage(),
                'agent' => $agent->id,
            ]);

            $assistantMessage->update([
                'content' => 'I encountered an error while processing your request. Please try again.',
                'is_streaming' => false,
                'is_complete' => true,
            ]);

            $aiInteraction->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            broadcast(new ConversationMessageStreaming(
                $assistantMessage,
                '',
                true
            ))->toOthers();
        }
    }

    /**
     * Prepare agent's tools in Ollama format
     */
    protected function prepareToolsForOllama(Agent $agent): array
    {
        if (! $agent->tools || $agent->tools->isEmpty()) {
            return [];
        }

        $tools = [];

        foreach ($agent->tools as $tool) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => Str::slug($tool->name, '_'),
                    'description' => $tool->description,
                    'parameters' => $tool->input_schema ?? [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * Execute tool calls (placeholder for now)
     */
    protected function executeToolCalls(array $toolCalls, ConversationMessage $message, Agent $agent): void
    {
        $results = [];

        foreach ($toolCalls as $toolCall) {
            // TODO: Implement actual tool execution
            $results[] = [
                'tool' => $toolCall['function']['name'] ?? 'unknown',
                'result' => 'Tool execution will be implemented in next phase',
                'success' => false,
            ];
        }

        $message->update([
            'tool_results' => $results,
        ]);
    }

    /**
     * Create AI interaction log entry
     */
    protected function createAiInteraction(Agent $agent, string $prompt, Conversation $conversation): AiInteraction
    {
        return AiInteraction::create([
            'type' => 'chat',
            'status' => 'processing',
            'model_provider' => $agent->model_provider,
            'model_name' => $agent->model_name,
            'temperature' => $agent->creativity_level,
            'system_prompt' => $agent->system_prompt,
            'prompt' => $prompt,
            'user_id' => $conversation->user_id,
            'agent_id' => $agent->id,
        ]);
    }

    /**
     * Get or create conversation for user
     */
    public function getOrCreateConversation(User $user, ?Agent $agent = null): Conversation
    {
        // Get default Katra agent if none specified
        if (! $agent) {
            $agent = Agent::where('is_default', true)->first();

            if (! $agent) {
                throw new \Exception('Default Katra agent not found. Please run the seeder.');
            }
        }

        // Check for recent conversation
        $conversation = Conversation::where('user_id', $user->id)
            ->where('agent_id', $agent->id)
            ->latest()
            ->first();

        // Create new conversation if none exists or last one is old
        if (! $conversation || $conversation->updated_at->lt(now()->subHours(1))) {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'agent_id' => $agent->id,
            ]);
        }

        return $conversation;
    }
}
