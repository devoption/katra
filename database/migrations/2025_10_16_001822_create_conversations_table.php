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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Ownership
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('agent_id')->constrained('agents'); // Default agent for this conversation

            // Metadata
            $table->string('title')->nullable(); // Auto-generated from first message
            $table->json('metadata')->nullable(); // Settings, preferences, etc.

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('agent_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
