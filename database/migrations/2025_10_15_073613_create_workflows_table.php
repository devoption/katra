<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('version')->default('1.0');
            $table->json('definition');
            $table->enum('execution_mode', ['series', 'parallel', 'dag']);
            $table->boolean('is_active')->default(true);
            $table->foreignId('context_id')->nullable()->constrained('contexts');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
