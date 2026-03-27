<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workspace_chats', function (Blueprint $table): void {
            $table->boolean('has_agent_participant')->default(false)->after('summary');
        });

        DB::table('workspace_chat_participants')
            ->select('chat_id')
            ->where('participant_type', 'agent')
            ->distinct()
            ->orderBy('chat_id')
            ->chunk(100, function ($chatIdsWithAgents): void {
                $chatIds = $chatIdsWithAgents
                    ->pluck('chat_id')
                    ->map(fn (mixed $chatId): int => (int) $chatId)
                    ->all();

                if ($chatIds !== []) {
                    DB::table('workspace_chats')
                        ->whereIn('id', $chatIds)
                        ->update(['has_agent_participant' => true]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_chats', function (Blueprint $table): void {
            $table->dropColumn('has_agent_participant');
        });
    }
};
