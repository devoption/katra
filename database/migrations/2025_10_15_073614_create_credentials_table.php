<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // api_key, oauth, password, certificate, custom
            $table->string('provider')->nullable(); // openai, github, etc.
            $table->text('encrypted_value');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('provider');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};
