@extends('auth.layout')

@section('title', 'Connect to a Katra server')
@section('heading', 'Connect to a server')
@section('copy')
    @if (isset($instanceConnection) || filled($pendingServerUrl ?? null))
        Sign in to {{ $instanceConnection->name ?? $pendingServerName }} and attach this device to that Katra server.
    @else
        Use your server account when you want to sign in to a remote Katra instance.
    @endif
@endsection
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
    @if (isset($instanceConnection) || filled($pendingServerUrl ?? null))
        <form method="POST" action="{{ isset($instanceConnection) ? route('connections.authenticate', $instanceConnection) : route('server.connect.authenticate') }}" class="space-y-6">
            @csrf

            <div class="shell-panel rounded-[24px] px-4 py-4">
                <p class="shell-text text-sm font-medium">{{ $instanceConnection->name ?? $pendingServerName }}</p>
                <p class="shell-text-soft mt-1 text-sm">{{ $instanceConnection->base_url ?? $pendingServerUrl }}</p>
            </div>

            <div class="space-y-2">
                <label for="email" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="username"
                    required
                    value="{{ old('email', data_get($instanceConnection ?? null, 'session_context.email')) }}"
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
                    required
                    class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
                />
            </div>

            <button type="submit" class="shell-accent-chip mt-3 flex h-12 w-full items-center justify-center rounded-[18px] text-sm font-semibold transition hover:opacity-90">
                Connect to server
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('server.connect.prepare') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label for="server_url" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Server URL</label>
                <input
                    id="server_url"
                    name="server_url"
                    type="url"
                    autocomplete="url"
                    value="{{ old('server_url') }}"
                    placeholder="https://katra.example.com"
                    class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
                    required
                />
            </div>

            <button type="submit" class="shell-accent-chip mt-3 flex h-12 w-full items-center justify-center rounded-[18px] text-sm font-semibold transition hover:opacity-90">
                Continue to server
            </button>

            <p class="shell-text-soft text-sm leading-6">
                Continue into the selected Katra server without leaving Katra. The next step stays inside the client and asks for that server account.
            </p>
        </form>
    @endif
@endsection
