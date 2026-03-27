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

        Schema::table('connection_workspaces', function (Blueprint $table) use ($driver): void {
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('active_chat_id')->nullable()->after('summary');
            } else {
                $table->foreignId('active_chat_id')
                    ->nullable()
                    ->after('summary')
                    ->constrained('workspace_chats')
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

        Schema::table('connection_workspaces', function (Blueprint $table) use ($driver): void {
            if ($driver !== 'surreal') {
                $table->dropForeign(['active_chat_id']);
            }

            $table->dropColumn('active_chat_id');
        });
    }
};
