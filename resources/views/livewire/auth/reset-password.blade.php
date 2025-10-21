<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-nord0 dark:text-nord6">Create new password</h2>
        <p class="mt-2 text-sm text-nord3 dark:text-nord4">
            Enter your new password below
        </p>
    </div>

    <!-- Reset Password Form -->
    <form wire:submit="resetPassword" class="space-y-6">
        <!-- Email (readonly) -->
        <x-ui.input
            wire:model="email"
            type="email"
            name="email"
            label="Email"
            readonly
            :error="$errors->first('email')"
        />

        <!-- Password -->
        <x-ui.input
            wire:model="password"
            type="password"
            name="password"
            label="New Password"
            placeholder="••••••••"
            required
            autofocus
            :error="$errors->first('password')"
            help="Must be at least 8 characters"
        />

        <!-- Confirm Password -->
        <x-ui.input
            wire:model="password_confirmation"
            type="password"
            name="password_confirmation"
            label="Confirm New Password"
            placeholder="••••••••"
            required
            :error="$errors->first('password_confirmation')"
        />

        <!-- Submit Button -->
        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>Reset Password</span>
            <span wire:loading class="flex items-center justify-center">
                <x-ui.loading size="sm" class="mr-2" />
                Resetting...
            </span>
        </x-ui.button>
    </form>

    <!-- Back to Login Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-primary hover:text-nord7 transition-colors inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to login
        </a>
    </div>
</div>
