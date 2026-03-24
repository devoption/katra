<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'Katra'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-mono:400,500|space-grotesk:400,500,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="shell-app min-h-screen antialiased">
        <main class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8">
            <section class="w-full max-w-lg space-y-5">
                <div class="space-y-4">
                    <img src="{{ asset('katra-logo.svg') }}" alt="Katra" class="shell-logo-dark h-8 w-auto" />
                    <img src="{{ asset('katra-logo-light.svg') }}" alt="Katra" class="shell-logo-light h-8 w-auto" />
                </div>

                <div class="shell-surface rounded-[32px] p-6 shadow-[var(--shell-shadow)] sm:p-8">
                    @hasSection('account_selector')
                        <div class="mb-6">
                            @yield('account_selector')
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-5 rounded-[18px] bg-[var(--shell-accent-soft)] px-4 py-3 text-sm text-[color:var(--shell-text)]">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-[18px] bg-[var(--shell-danger-soft)] px-4 py-3 text-sm text-[color:var(--shell-text)]">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6 space-y-3">
                        <p class="shell-text-info font-mono text-[10px] uppercase tracking-[0.14em]">@yield('eyebrow', 'Authentication')</p>
                        <h1 class="shell-text text-3xl font-semibold tracking-[-0.04em]">@yield('heading')</h1>
                        <p class="shell-text-soft text-sm leading-6">@yield('copy')</p>
                    </div>

                    @yield('content')
                </div>
            </section>
        </main>
    </body>
</html>
