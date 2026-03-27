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
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('workspace_chat_participants', function (Blueprint $table) use ($driver): void {
            $table->id();
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('chat_id');
                $table->unsignedBigInteger('user_id')->nullable();
            } else {
                $table->foreignId('chat_id')->constrained('workspace_chats')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }
            $table->string('participant_type', 25);
            $table->string('participant_key');
            $table->string('display_name');
            $table->timestamps();

            if ($driver !== 'surreal') {
                $table->unique(['chat_id', 'participant_key'], 'workspace_chat_participants_chat_key_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_chat_participants');
    }
};
