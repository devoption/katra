<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_credential', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->onDelete('cascade');
            $table->foreignId('credential_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['tool_id', 'credential_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_credential');
    }
};
