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
        if (! Schema::hasTable('workspace_agents')) {
            return;
        }

        if (! Schema::hasColumn('workspace_agents', 'agent_key') || ! Schema::hasColumn('workspace_agents', 'key')) {
            return;
        }

        Schema::table('workspace_agents', function (Blueprint $table): void {
            $table->dropColumn('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('workspace_agents') || Schema::hasColumn('workspace_agents', 'key')) {
            return;
        }

        Schema::table('workspace_agents', function (Blueprint $table): void {
            $table->string('key')->nullable()->after('workspace_id');
        });
    }
};
