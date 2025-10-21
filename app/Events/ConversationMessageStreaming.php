<?php

namespace App\Events;

use App\Models\ConversationMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationMessageStreaming implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ConversationMessage $message,
        public string $chunk,
        public bool $done
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversations.'.$this->message->conversation_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'chunk' => $this->chunk,
            'done' => $this->done,
            'content' => $this->message->content,
            'tool_calls' => $this->message->tool_calls,
            'tool_results' => $this->message->tool_results,
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.streaming';
    }
}
