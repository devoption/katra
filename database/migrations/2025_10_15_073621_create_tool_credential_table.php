<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credential_tool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credential_id')->constrained()->onDelete('cascade');
            $table->foreignId('tool_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['credential_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credential_tool');
    }
};
