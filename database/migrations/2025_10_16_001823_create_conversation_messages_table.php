<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('agents'); // Which agent responded (for agent switching)
            $table->foreignId('ai_interaction_id')->nullable()->constrained('ai_interactions'); // Link to logging

            // Message Data
            $table->string('role'); // user, assistant, system, tool
            $table->longText('content')->nullable(); // The message text
            $table->json('tool_calls')->nullable(); // Tool calls made by assistant
            $table->json('tool_results')->nullable(); // Results from tool executions

            // Streaming State
            $table->boolean('is_streaming')->default(false);
            $table->boolean('is_complete')->default(false);

            // Metadata
            $table->json('metadata')->nullable(); // Model used, tokens, cost, etc.

            $table->timestamps();

            // Indexes
            $table->index('conversation_id');
            $table->index('agent_id');
            $table->index('created_at');
            $table->index(['conversation_id', 'created_at']); // For fetching messages in order
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};
