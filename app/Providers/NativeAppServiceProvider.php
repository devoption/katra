<?php

namespace App\Providers;

use App\Support\Native\NativeRuntimePersistence;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        app(NativeRuntimePersistence::class)->configure();

        Window::open()
            ->url(route('home'))
            ->title(config('app.name'))
            ->width(1480)
            ->height(960)
            ->minWidth(1180)
            ->minHeight(760)
            ->rememberState();
    }

    public function phpIni(): array
    {
        return [];
    }
}
