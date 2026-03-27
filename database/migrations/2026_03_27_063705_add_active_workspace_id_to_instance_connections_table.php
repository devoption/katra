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

        Schema::table('instance_connections', function (Blueprint $table) use ($driver) {
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('active_workspace_id')->nullable()->after('base_url');
            } else {
                $table->foreignId('active_workspace_id')
                    ->nullable()
                    ->after('base_url')
                    ->constrained('connection_workspaces')
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

        Schema::table('instance_connections', function (Blueprint $table) use ($driver) {
            if ($driver !== 'surreal') {
                $table->dropForeign(['active_workspace_id']);
            }

            $table->dropColumn('active_workspace_id');
        });
    }
};
