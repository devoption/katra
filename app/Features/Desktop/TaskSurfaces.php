<?php

namespace App\Features\Desktop;

use Laravel\Pennant\Attributes\Name;

#[Name('ui.desktop.task-surfaces')]
class TaskSurfaces
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return false;
    }
}
