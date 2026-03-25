<?php

namespace App\Features\Desktop;

use Laravel\Pennant\Attributes\Name;

#[Name('ui.desktop.mvp-shell')]
class MvpShell
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return true;
    }
}
