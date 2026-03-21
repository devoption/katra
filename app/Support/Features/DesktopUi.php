<?php

namespace App\Support\Features;

use App\Features\Desktop\AgentPresence;
use App\Features\Desktop\ArtifactSurfaces;
use App\Features\Desktop\ConversationChannels;
use App\Features\Desktop\MvpShell;
use App\Features\Desktop\TaskSurfaces;
use App\Features\Desktop\WorkspaceNavigation;
use Laravel\Pennant\Feature;

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
        return Feature::for(self::scope())->active($feature);
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
        return Feature::for(self::scope())->values(self::features());
    }
}
