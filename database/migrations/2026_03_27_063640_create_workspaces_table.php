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

        if ($driver === 'surreal' && Schema::hasTable('workspaces')) {
            Schema::drop('workspaces');
        }

        Schema::create('workspaces', function (Blueprint $table) use ($driver) {
            $table->id();
            if ($driver === 'surreal') {
                $table->unsignedBigInteger('instance_connection_id');
            } else {
                $table->foreignId('instance_connection_id')->constrained()->cascadeOnDelete();
            }
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->timestamps();

            if ($driver !== 'surreal') {
                $table->unique(['instance_connection_id', 'slug'], 'workspaces_connection_slug_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
