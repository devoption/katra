@extends('auth.layout')

@section('title', 'Sign in to Katra')
@section('heading', 'Sign in to Katra')
@section('copy', 'Choose how you want to sign in and continue into Katra.')
@section('account_selector')
    <div class="shell-panel grid grid-cols-2 gap-2 rounded-[18px] p-1.5">
        <a href="{{ route('login') }}" class="shell-accent-chip flex h-11 items-center justify-center rounded-[14px] text-sm font-semibold">
            This instance
        </a>
        <a href="{{ route('server.connect') }}" class="shell-text-soft shell-hover-surface flex h-11 items-center justify-center rounded-[14px] text-sm font-medium transition-colors">
            Server
        </a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div class="space-y-2">
            <label for="email" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="username"
                required
                autofocus
                value="{{ old('email') }}"
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between gap-4">
                <label for="password" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Password</label>
                <a href="{{ route('password.request') }}" class="shell-text-info text-sm font-medium hover:opacity-80">Forgot password?</a>
            </div>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <label class="shell-text-soft flex items-center gap-3 text-sm">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-none accent-[var(--shell-accent)]" />
            <span>Keep me signed in</span>
        </label>

        <button type="submit" class="shell-accent-chip mt-2 flex h-12 w-full items-center justify-center rounded-[18px] text-sm font-semibold transition hover:opacity-90">
            Sign in
        </button>
    </form>

    <p class="shell-text-soft mt-8 text-sm">
        Need an account?
        <a href="{{ route('register') }}" class="shell-text-info font-medium hover:opacity-80">Create one now</a>
    </p>
@endsection
