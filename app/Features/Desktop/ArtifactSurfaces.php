<?php

namespace App\Features\Desktop;

use Laravel\Pennant\Attributes\Name;

#[Name('ui.desktop.artifact-surfaces')]
class ArtifactSurfaces
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return false;
    }
}
