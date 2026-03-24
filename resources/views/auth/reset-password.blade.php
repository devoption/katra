@extends('auth.layout')

@section('title', 'Choose a new Katra password')
@section('heading', 'Choose a new password')
@section('copy', 'Finish recovering your local Katra account.')
@section('panel_copy', 'Set a new password for this account, then Fortify will send you straight back into Katra.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}" />

        <div class="space-y-2">
            <label for="email" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="username"
                required
                value="{{ old('email', $request->email) }}"
                class="shell-panel shell-text h-12 w-full rounded-[18px] border border-transparent px-4 text-sm outline-none transition focus:border-[color:var(--shell-info)]"
            />
        </div>

        <div class="space-y-2">
            <label for="password" class="shell-text-faint block font-mono text-[10px] uppercase tracking-[0.14em]">New password</label>
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
            Reset password
        </button>
    </form>

    <p class="shell-text-soft mt-6 text-sm">
        Want to try signing in again?
        <a href="{{ route('login') }}" class="shell-text-info font-medium hover:opacity-80">Back to sign in</a>
    </p>
@endsection
