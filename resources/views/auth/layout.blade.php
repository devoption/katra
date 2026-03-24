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
        <main class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(24rem,30rem)]">
            <section class="shell-panel hidden min-h-screen flex-col justify-between px-8 py-8 lg:flex xl:px-12">
                <div class="space-y-8">
                    <div class="space-y-6">
                        <img src="{{ asset('katra-logo.svg') }}" alt="Katra" class="shell-logo-dark h-9 w-auto" />
                        <img src="{{ asset('katra-logo-light.svg') }}" alt="Katra" class="shell-logo-light h-9 w-auto" />

                        <div class="max-w-xl space-y-3">
                            <p class="shell-text-info font-mono text-[11px] uppercase tracking-[0.16em]">Local authentication</p>
                            <h1 class="shell-text text-4xl font-semibold tracking-[-0.04em]">
                                @yield('heading')
                            </h1>
                            <p class="shell-text-soft text-base leading-7">
                                @yield('copy')
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-2">
                        <div class="shell-surface rounded-[28px] p-5">
                            <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">Secure by default</p>
                            <p class="shell-text mt-3 text-sm leading-6">
                                Session auth now belongs to the same Laravel foundation the rest of the app is built on.
                            </p>
                        </div>

                        <div class="shell-surface rounded-[28px] p-5">
                            <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">Local-first account</p>
                            <p class="shell-text mt-3 text-sm leading-6">
                                Create a local account here first, then keep layering on remote connections and richer workspace controls.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">
                    Katra desktop auth foundation
                </p>
            </section>

            <section class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8">
                <div class="w-full max-w-md space-y-5">
                    <div class="space-y-4 lg:hidden">
                        <img src="{{ asset('katra-logo.svg') }}" alt="Katra" class="shell-logo-dark h-8 w-auto" />
                        <img src="{{ asset('katra-logo-light.svg') }}" alt="Katra" class="shell-logo-light h-8 w-auto" />
                        <div>
                            <p class="shell-text-info font-mono text-[10px] uppercase tracking-[0.14em]">@yield('mobile_eyebrow', 'Authentication')</p>
                            <h1 class="shell-text mt-2 text-3xl font-semibold tracking-[-0.04em]">@yield('heading')</h1>
                            <p class="shell-text-soft mt-3 text-sm leading-6">@yield('copy')</p>
                        </div>
                    </div>

                    <div class="shell-surface rounded-[32px] p-6 shadow-[var(--shell-shadow)] sm:p-8">
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

                        <div class="mb-6 hidden space-y-3 lg:block">
                            <p class="shell-text-info font-mono text-[10px] uppercase tracking-[0.14em]">@yield('desktop_eyebrow', 'Authentication')</p>
                            <h2 class="shell-text text-3xl font-semibold tracking-[-0.04em]">@yield('heading')</h2>
                            <p class="shell-text-soft text-sm leading-6">@yield('panel_copy')</p>
                        </div>

                        @yield('content')
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
