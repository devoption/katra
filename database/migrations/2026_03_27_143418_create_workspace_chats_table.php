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

        Schema::create('workspace_chats', function (Blueprint $table) use ($driver): void {
            $table->id();
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('workspace_id');
            } else {
                $table->foreignId('workspace_id')->constrained('connection_workspaces')->cascadeOnDelete();
            }
            $table->string('name');
            $table->string('slug');
            $table->string('kind', 25);
            $table->string('visibility', 25);
            $table->text('summary')->nullable();
            $table->timestamps();

            if ($driver !== 'surreal') {
                $table->unique(['workspace_id', 'slug'], 'workspace_chats_workspace_slug_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_chats');
    }
};
