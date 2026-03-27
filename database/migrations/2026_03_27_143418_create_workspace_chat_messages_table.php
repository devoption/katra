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

        Schema::create('workspace_chat_messages', function (Blueprint $table) use ($driver): void {
            $table->id();
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('chat_id');
            } else {
                $table->foreignId('chat_id')->constrained('workspace_chats')->cascadeOnDelete();
            }
            $table->string('sender_type', 25);
            $table->string('sender_key')->nullable();
            $table->string('sender_name');
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_chat_messages');
    }
};
