<?php

namespace App\Support\Features;

use App\Features\Desktop\AgentPresence;
use App\Features\Desktop\ArtifactSurfaces;
use App\Features\Desktop\ConversationChannels;
use App\Features\Desktop\MvpShell;
use App\Features\Desktop\TaskSurfaces;
use App\Features\Desktop\WorkspaceNavigation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Laravel\Pennant\Attributes\Name;
use Laravel\Pennant\Feature;
use ReflectionClass;

class DesktopUi
{
    /**
     * The Pennant scope used for desktop UI rollout flags.
     */
    public static function scope(): string
    {
        return 'desktop-ui';
    }

    /**
     * The ordered list of desktop UI rollout features.
     *
     * @return list<class-string>
     */
    public static function features(): array
    {
        return [
            MvpShell::class,
            WorkspaceNavigation::class,
            ConversationChannels::class,
            TaskSurfaces::class,
            ArtifactSurfaces::class,
            AgentPresence::class,
        ];
    }

    /**
     * Determine if the given desktop UI feature is active.
     *
     * @param  class-string  $feature
     */
    public static function active(string $feature): bool
    {
        return self::enabled(self::states(), $feature);
    }

    /**
     * Determine if the given desktop UI feature is enabled in the provided state map.
     *
     * @param  array<string, mixed>  $states
     * @param  class-string  $feature
     */
    public static function enabled(array $states, string $feature): bool
    {
        return (bool) ($states[self::featureName($feature)] ?? self::defaultValue($feature));
    }

    public static function mvpShellEnabled(): bool
    {
        return self::active(MvpShell::class);
    }

    public static function workspaceNavigationEnabled(): bool
    {
        return self::active(WorkspaceNavigation::class);
    }

    public static function conversationChannelsEnabled(): bool
    {
        return self::active(ConversationChannels::class);
    }

    public static function taskSurfacesEnabled(): bool
    {
        return self::active(TaskSurfaces::class);
    }

    public static function artifactSurfacesEnabled(): bool
    {
        return self::active(ArtifactSurfaces::class);
    }

    public static function agentPresenceEnabled(): bool
    {
        return self::active(AgentPresence::class);
    }

    /**
     * Resolve the current desktop UI rollout state.
     *
     * @return array<string, mixed>
     */
    public static function states(): array
    {
        if (! self::databaseStoreReady()) {
            return self::defaultStates();
        }

        try {
            return Feature::for(self::scope())->values(self::features());
        } catch (QueryException $exception) {
            if (self::missingFeatureTable($exception)) {
                return self::defaultStates();
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultStates(): array
    {
        $states = [];

        foreach (self::features() as $feature) {
            $states[self::featureName($feature)] = self::defaultValue($feature);
        }

        return $states;
    }

    /**
     * @param  class-string  $feature
     */
    private static function defaultValue(string $feature): mixed
    {
        return app($feature)->resolve(self::scope());
    }

    /**
     * @param  class-string  $feature
     */
    private static function featureName(string $feature): string
    {
        $attributes = (new ReflectionClass($feature))->getAttributes(Name::class);

        if ($attributes === []) {
            return $feature;
        }

        return $attributes[0]->newInstance()->name;
    }

    private static function databaseStoreReady(): bool
    {
        if (config('pennant.default') !== 'database') {
            return true;
        }

        $connection = config('pennant.stores.database.connection');
        $table = config('pennant.stores.database.table', 'features');

        return Schema::connection($connection)->hasTable($table);
    }

    private static function missingFeatureTable(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'no such table: features');
    }
}
