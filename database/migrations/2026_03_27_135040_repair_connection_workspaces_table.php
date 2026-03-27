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
        $driver = Schema::getConnection()->getDriverName();

        if (! Schema::hasTable('connection_workspaces')) {
            Schema::create('connection_workspaces', function (Blueprint $table) use ($driver): void {
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

        if (! Schema::hasTable('workspaces') || ! Schema::hasColumn('workspaces', 'instance_connection_id')) {
            return;
        }

        $legacyWorkspaces = DB::table('workspaces')
            ->whereNotNull('instance_connection_id')
            ->get([
                'id',
                'instance_connection_id',
                'name',
                'slug',
                'summary',
                'created_at',
                'updated_at',
            ]);

        if ($legacyWorkspaces->isEmpty()) {
            return;
        }

        $existingIds = collect(DB::table('connection_workspaces')->get())
            ->map(fn (object $workspace): int|string|null => $workspace->id ?? null)
            ->filter()
            ->values()
            ->all();

        $workspacePayload = $legacyWorkspaces
            ->reject(fn (object $workspace): bool => in_array($workspace->id, $existingIds, true))
            ->map(fn (object $workspace): array => [
                'id' => $workspace->id,
                'instance_connection_id' => $workspace->instance_connection_id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'summary' => $workspace->summary,
                'created_at' => $workspace->created_at,
                'updated_at' => $workspace->updated_at,
            ])
            ->values()
            ->all();

        if ($workspacePayload === []) {
            $this->syncSurrealSequence($driver);

            return;
        }

        DB::table('connection_workspaces')->insert($workspacePayload);

        $this->syncSurrealSequence($driver);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connection_workspaces');
    }

    private function syncSurrealSequence(string $driver): void
    {
        if ($driver !== 'surreal') {
            return;
        }

        $maxId = collect(DB::table('connection_workspaces')->get(['id']))
            ->map(fn (object $workspace): int => (int) ($workspace->id ?? 0))
            ->max();

        if (! is_int($maxId) || $maxId <= 0) {
            return;
        }

        DB::statement(sprintf(
            'UPSERT ONLY __katra_sequences:connection_workspaces SET value = %d;',
            $maxId,
        ));
    }
};
