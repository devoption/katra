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

        Schema::table('workspace_chat_participants', function (Blueprint $table) use ($driver): void {
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('workspace_agent_id')->nullable()->after('user_id');
            } else {
                $table->foreignId('workspace_agent_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('workspace_agents')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('workspace_chat_participants', function (Blueprint $table) use ($driver): void {
            if ($driver === 'surreal') {
                $table->dropColumn('workspace_agent_id');

                return;
            }

            $table->dropConstrainedForeignId('workspace_agent_id');
        });
    }
};
