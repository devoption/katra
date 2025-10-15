<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-nord0 dark:text-nord6">Reset your password</h2>
        <p class="mt-2 text-sm text-nord3 dark:text-nord4">
            Enter your email and we'll send you a link to reset your password
        </p>
    </div>

    <!-- Success Message -->
    @if($status)
        <x-ui.alert type="success" class="mb-6" dismissible>
            {{ $status }}
        </x-ui.alert>
    @endif

    <!-- Forgot Password Form -->
    <form wire:submit="sendResetLink" class="space-y-6">
        <!-- Email -->
        <x-ui.input
            wire:model="email"
            type="email"
            name="email"
            label="Email"
            placeholder="you@example.com"
            required
            autofocus
            :error="$errors->first('email')"
        />

        <!-- Submit Button -->
        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>Send Reset Link</span>
            <span wire:loading class="flex items-center justify-center">
                <x-ui.loading size="sm" class="mr-2" />
                Sending...
            </span>
        </x-ui.button>
    </form>

    <!-- Back to Login Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-nord8 hover:text-nord7 transition-colors inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to login
        </a>
    </div>
</div>
