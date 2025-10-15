<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['builtin', 'custom', 'mcp_server', 'package']);
            $table->string('category')->nullable();
            $table->json('input_schema');
            $table->json('output_schema')->nullable();
            $table->string('execution_method')->nullable();
            $table->json('execution_config')->nullable();
            $table->boolean('requires_credential')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
