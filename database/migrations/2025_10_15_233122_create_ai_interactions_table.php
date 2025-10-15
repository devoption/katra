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
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Interaction Type & Context
            $table->string('type'); // chat, workflow_execution, agent_step, tool_execution
            $table->string('status')->default('pending'); // pending, processing, success, error, timeout

            // Model Configuration
            $table->string('model_provider')->nullable(); // openai, anthropic, ollama, google
            $table->string('model_name')->nullable(); // gpt-4, claude-3-sonnet, etc.
            $table->decimal('temperature', 3, 2)->nullable(); // 0.00 - 2.00
            $table->integer('max_tokens')->nullable();

            // Input/Output Data (Critical for training)
            $table->longText('system_prompt')->nullable();
            $table->longText('prompt'); // User input / agent instruction
            $table->longText('response')->nullable(); // AI output
            $table->json('messages')->nullable(); // Full conversation history for chats
            $table->json('tool_calls')->nullable(); // If AI used tools
            $table->json('tool_results')->nullable(); // Tool execution results

            // Performance Metrics
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->integer('latency_ms')->nullable(); // Response time
            $table->decimal('cost_usd', 10, 6)->nullable(); // Estimated cost

            // Error Handling
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();

            // Relationships
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('agent_id')->nullable()->constrained('agents');
            $table->foreignId('workflow_execution_id')->nullable()->constrained('workflow_executions');
            $table->foreignId('workflow_step_id')->nullable()->constrained('workflow_steps');
            $table->foreignId('parent_interaction_id')->nullable()->constrained('ai_interactions'); // For threaded conversations

            // Training Data Metadata
            $table->json('metadata')->nullable(); // Additional context: user intent, tags, categories
            $table->boolean('include_in_training')->default(false); // Opt-in for model training
            $table->decimal('quality_score', 3, 2)->nullable(); // 0.00 - 1.00 (automated quality assessment)

            $table->timestamps();
            $table->softDeletes();

            // Indexes for efficient querying
            $table->index('type');
            $table->index('status');
            $table->index('model_provider');
            $table->index('user_id');
            $table->index('agent_id');
            $table->index('workflow_execution_id');
            $table->index('created_at');
            $table->index('include_in_training');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};
