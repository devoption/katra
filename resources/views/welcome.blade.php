<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Katra') }} Desktop Shell</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-mono:400,500|space-grotesk:400,500,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_#19324f_0%,_#0f172a_38%,_#050816_100%)] text-white antialiased">
        <div class="relative isolate overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-64 bg-[radial-gradient(circle_at_top,_rgba(84,166,255,0.22),_transparent_65%)]"></div>
            <div class="absolute -left-20 top-32 h-72 w-72 rounded-full bg-cyan-400/12 blur-3xl"></div>
            <div class="absolute right-0 top-12 h-96 w-96 rounded-full bg-sky-300/10 blur-3xl"></div>

            <main class="mx-auto flex min-h-screen max-w-7xl flex-col gap-10 px-6 py-8 lg:px-10 lg:py-10">
                <section class="rounded-[32px] border border-white/10 bg-white/6 p-3 shadow-2xl shadow-slate-950/40 backdrop-blur">
                    <div class="rounded-[26px] border border-white/10 bg-slate-950/65 px-5 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex gap-2">
                                    <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                                    <span class="h-3 w-3 rounded-full bg-amber-300"></span>
                                    <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                                </div>
                                <p class="font-mono text-xs uppercase tracking-[0.32em] text-sky-100/70">Katra Native Runtime</p>
                            </div>

                            <div class="rounded-full border border-white/10 bg-white/6 px-3 py-1 font-mono text-[11px] uppercase tracking-[0.28em] text-cyan-100/70">
                                local-first preview
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-8 lg:grid-cols-[minmax(0,1.35fr)_360px]">
                    <div class="space-y-8">
                        <div class="space-y-6">
                            <p class="font-mono text-xs uppercase tracking-[0.36em] text-cyan-200/75">NativePHP desktop shell</p>

                            <div class="max-w-4xl space-y-5">
                                <h1 class="max-w-3xl text-5xl font-bold tracking-[-0.05em] text-balance text-white sm:text-6xl lg:text-7xl">
                                    Katra is taking shape as a graph-native workspace for collaborative intelligence.
                                </h1>

                                <p class="max-w-2xl text-lg leading-8 text-slate-200/82 sm:text-xl">
                                    This first shell proves the Laravel app can launch inside NativePHP while keeping the Katra v2 direction visible:
                                    local-first workflows, graph-native context, multi-runtime delivery, and a desktop experience built intentionally from day one.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-3">
                            <article class="rounded-[28px] border border-cyan-200/12 bg-cyan-300/8 p-5">
                                <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-cyan-100/70">Runtime</p>
                                <h2 class="mt-4 text-2xl font-semibold text-white">Desktop-first loop</h2>
                                <p class="mt-3 text-sm leading-7 text-slate-200/78">
                                    Launch the app with <span class="font-mono text-cyan-100">composer native:dev</span> and iterate on a real desktop window while the Surreal foundation can auto-start a local runtime when the CLI is available.
                                </p>
                            </article>

                            <article class="rounded-[28px] border border-sky-200/12 bg-sky-300/8 p-5">
                                <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-sky-100/70">Graph</p>
                                <h2 class="mt-4 text-2xl font-semibold text-white">State over transcript</h2>
                                <p class="mt-3 text-sm leading-7 text-slate-200/78">
                                    Conversations, tasks, decisions, and artifacts are headed toward first-class graph objects, not disposable chat history.
                                </p>
                            </article>

                            <article class="rounded-[28px] border border-emerald-200/12 bg-emerald-300/8 p-5">
                                <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-emerald-100/70">Release</p>
                                <h2 class="mt-4 text-2xl font-semibold text-white">Bundled preview</h2>
                                <p class="mt-3 text-sm leading-7 text-slate-200/78">
                                    Downloadable macOS previews can now carry the local Surreal runtime instead of depending on a separately installed machine-local CLI.
                                </p>
                            </article>
                        </div>

                        <div class="rounded-[32px] border border-white/10 bg-slate-950/55 p-6 shadow-xl shadow-slate-950/30">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                                <div class="space-y-4">
                                    <p class="font-mono text-[11px] uppercase tracking-[0.34em] text-slate-300/70">Bootstrap status</p>
                                    <h2 class="text-3xl font-semibold tracking-[-0.04em] text-white">A minimal Katra shell is available for smoke testing now.</h2>
                                </div>

                                <div class="grid gap-3 text-sm text-slate-200/80 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-slate-300/70">Surreal Foundation</p>
                                        <p class="mt-2">
                                            <span class="font-mono uppercase tracking-[0.2em] {{ $surrealStatus === 'connected' ? 'text-emerald-200' : 'text-amber-200' }}">{{ $surrealStatus }}</span>
                                        </p>
                                        <p class="mt-2">{{ $surrealMessage }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-slate-300/70">Preview Workspace</p>
                                        @if ($workspace)
                                            <p class="mt-2 font-semibold text-white">{{ $workspace->name }}</p>
                                            <p class="mt-2 text-xs font-mono uppercase tracking-[0.22em] text-cyan-100/72">{{ $workspace->id }}</p>
                                            <p class="mt-2">{{ $workspace->summary }}</p>
                                        @else
                                            <p class="mt-2">The shell is ready, but no Surreal-backed preview workspace is available on this machine yet.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="rounded-[34px] border border-white/10 bg-slate-950/72 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur">
                        <div class="space-y-6">
                            <div>
                                <p class="font-mono text-[11px] uppercase tracking-[0.34em] text-slate-300/72">Launch kit</p>
                                <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-white">What to run locally</h2>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-2xl border border-cyan-200/12 bg-cyan-300/8 p-4">
                                    <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-cyan-100/72">Primary</p>
                                    <p class="mt-3 font-mono text-sm text-white">composer native:dev</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-200/76">Starts NativePHP and Vite together for the local desktop loop.</p>
                                </div>

                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-slate-300/72">Fallback</p>
                                    <p class="mt-3 font-mono text-sm text-white">php artisan native:run --no-interaction</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-200/76">Runs the desktop shell directly if you want to manage Vite separately.</p>
                                </div>
                            </div>

                            <div class="rounded-[28px] border border-white/10 bg-white/5 p-5">
                                <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-slate-300/72">Next layers</p>
                                <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-200/78">
                                    <li>Graph repositories and Surreal-backed model flows beyond the preview workspace</li>
                                    <li>Fortify auth and conversation scaffolding inside the desktop shell</li>
                                    <li>Signed desktop builds, auto-updates, and release polish</li>
                                </ul>
                            </div>

                            <div class="rounded-[28px] border border-amber-200/12 bg-amber-300/8 p-5">
                                <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-amber-100/78">Compatibility note</p>
                                <p class="mt-3 text-sm leading-7 text-slate-200/82">
                                    NativePHP does not yet ship an official Laravel 13 release. This bootstrap uses the upstream Laravel 13 compatibility branch so Katra can move forward while the official package catches up.
                                </p>
                            </div>
                        </div>
                    </aside>
                </section>
            </main>
        </div>
    </body>
</html>
