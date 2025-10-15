<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('role');
            $table->text('description')->nullable();
            $table->enum('model_provider', ['openai', 'anthropic', 'google', 'ollama', 'custom']);
            $table->string('model_name');
            $table->text('system_prompt');
            $table->decimal('creativity_level', 3, 2)->default(0.70);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('context_id')->nullable()->constrained('contexts');
            $table->foreignId('credential_id')->nullable()->constrained('credentials');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_default');
            $table->index('is_active');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
