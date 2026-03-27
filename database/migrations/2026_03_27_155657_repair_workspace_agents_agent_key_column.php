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
        if (! Schema::hasTable('workspace_agents') || Schema::hasColumn('workspace_agents', 'agent_key')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::table('workspace_agents', function (Blueprint $table): void {
            $table->string('agent_key')->nullable()->after('workspace_id');
        });

        $connection = DB::connection();
        $workspaceAgents = $connection->table('workspace_agents')->get();

        foreach ($workspaceAgents as $workspaceAgent) {
            $agentKey = $workspaceAgent->key ?? null;

            if (! is_string($agentKey) || trim($agentKey) === '') {
                continue;
            }

            $connection->table('workspace_agents')
                ->where('id', $workspaceAgent->id)
                ->update(['agent_key' => $agentKey]);
        }

        if ($driver !== 'surreal') {
            Schema::table('workspace_agents', function (Blueprint $table): void {
                $table->unique(['workspace_id', 'agent_key'], 'workspace_agents_workspace_agent_key_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('workspace_agents') || ! Schema::hasColumn('workspace_agents', 'agent_key')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'surreal') {
            Schema::table('workspace_agents', function (Blueprint $table): void {
                $table->dropUnique('workspace_agents_workspace_agent_key_unique');
            });
        }

        Schema::table('workspace_agents', function (Blueprint $table): void {
            $table->dropColumn('agent_key');
        });
    }
};
