<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workflow_execution_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained();
            $table->string('step_name');
            $table->json('step_definition');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'skipped']);
            $table->integer('order');
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->text('logs')->nullable();
            $table->json('error_data')->nullable();
            $table->string('container_id')->nullable();
            $table->decimal('cost', 10, 4)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('workflow_execution_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
