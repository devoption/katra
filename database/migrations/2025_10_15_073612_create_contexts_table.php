<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contexts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['agent', 'workflow', 'execution']);
            $table->json('content');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contexts');
    }
};
