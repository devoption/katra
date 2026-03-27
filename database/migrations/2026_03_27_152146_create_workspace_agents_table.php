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

        Schema::create('workspace_agents', function (Blueprint $table) use ($driver): void {
            $table->id();
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('workspace_id');
            } else {
                $table->foreignId('workspace_id')->constrained('connection_workspaces')->cascadeOnDelete();
            }
            $table->string('agent_key');
            $table->string('name');
            $table->string('agent_class');
            $table->text('summary')->nullable();
            $table->timestamps();

            if ($driver !== 'surreal') {
                $table->unique(['workspace_id', 'agent_key'], 'workspace_agents_workspace_agent_key_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_agents');
    }
};
