<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_tool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->foreignId('tool_id')->constrained()->onDelete('cascade');
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['agent_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_tool');
    }
};
