@extends('auth.layout')

@section('title', 'Create your Katra account')
@section('heading', 'Create your Katra account')
@section('copy', 'Set up the local account that unlocks the Katra desktop workspace.')
@section('panel_copy', 'This keeps the first auth step small and local while we prepare the broader connection and workspace model.')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div class="space-y-2">
            <label for="name" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Name</label>
            <input
                id="name"
                name="name"
                type="text"
                autocomplete="name"
                required
                value="{{ old('name') }}"
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
                required
                value="{{ old('email') }}"
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <div class="space-y-2">
            <label for="password" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
                required
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <div class="space-y-2">
            <label for="password_confirmation" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Confirm password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <button type="submit" class="shell-accent-chip flex h-12 w-full items-center justify-center rounded-[18px] text-sm font-semibold transition hover:opacity-90">
            Create account
        </button>
    </form>

    <p class="shell-text-soft mt-6 text-sm">
        Already have an account?
        <a href="{{ route('login') }}" class="shell-text-info font-medium hover:opacity-80">Sign in instead</a>
    </p>
@endsection
