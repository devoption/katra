<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workflow_id')->constrained();
            $table->string('workflow_version');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled']);
            $table->enum('triggered_by', ['user', 'agent', 'workflow', 'event', 'schedule', 'webhook']);
            $table->unsignedBigInteger('triggered_by_id')->nullable();
            $table->foreignId('context_id')->nullable()->constrained('contexts');
            $table->json('container_config')->nullable();
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->json('error_data')->nullable();
            $table->json('metrics')->nullable();
            $table->decimal('total_cost', 10, 4)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('workflow_id');
            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_executions');
    }
};
