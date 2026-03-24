@extends('auth.layout')

@section('title', 'Reset your Katra password')
@section('heading', 'Reset your password')
@section('copy', 'Request a reset link for your local Katra account.')
@section('panel_copy', 'We will send a password reset link to the email address tied to this local account.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

        <button type="submit" class="shell-accent-chip flex h-12 w-full items-center justify-center rounded-[18px] text-sm font-semibold transition hover:opacity-90">
            Email reset link
        </button>
    </form>

    <p class="shell-text-soft mt-6 text-sm">
        Remembered your password?
        <a href="{{ route('login') }}" class="shell-text-info font-medium hover:opacity-80">Back to sign in</a>
    </p>
@endsection
