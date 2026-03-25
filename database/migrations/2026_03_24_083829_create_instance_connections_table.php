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

        Schema::create('instance_connections', function (Blueprint $table) use ($driver) {
            $table->id();

            if ($driver === 'surreal') {
                $table->unsignedBigInteger('user_id');
            } else {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            }

            $table->string('name');
            $table->string('kind');
            $table->string('base_url')->nullable();
            $table->text('session_context')->nullable();
            $table->timestamp('last_authenticated_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            if ($driver !== 'surreal') {
                $table->unique(['user_id', 'kind', 'base_url'], 'instance_connections_user_kind_url_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instance_connections');
    }
};
