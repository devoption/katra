@extends('auth.layout')

@section('title', 'Connect to a Katra server')
@section('heading', 'Connect to a server')
@section('copy', 'Use your server account when you want to sign in to a remote Katra instance.')
@section('account_selector')
    <div class="shell-panel grid grid-cols-2 gap-2 rounded-[18px] p-1.5">
        <a href="{{ route('login') }}" class="shell-text-soft shell-hover-surface flex h-11 items-center justify-center rounded-[14px] text-sm font-medium transition-colors">
            This instance
        </a>
        <a href="{{ route('server.connect') }}" class="shell-accent-chip flex h-11 items-center justify-center rounded-[14px] text-sm font-semibold">
            Server
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="space-y-2">
            <label for="server_url" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Server URL</label>
            <input
                id="server_url"
                name="server_url"
                type="url"
                autocomplete="url"
                placeholder="https://katra.example.com"
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <div class="space-y-2">
            <label for="email" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="username"
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <div class="space-y-2">
            <label for="password" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <button type="button" class="shell-accent-chip mt-2 flex h-12 w-full items-center justify-center rounded-[18px] text-sm font-semibold opacity-80">
            Continue
        </button>

        <p class="shell-text-soft mt-8 text-sm leading-6">
            Remote connection profiles are next. This view keeps the account choice visible now so the shared auth experience does not overfit the desktop-only path.
        </p>
    </div>
@endsection
