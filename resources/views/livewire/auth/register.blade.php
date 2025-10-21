<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-nord0 dark:text-nord6">Create your account</h2>
        <p class="mt-2 text-sm text-nord3 dark:text-nord4">Join Katra and start automating</p>
    </div>

    <!-- Register Form -->
    <form wire:submit="register" class="space-y-6">
        <!-- Name Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-ui.input
                wire:model="first_name"
                type="text"
                name="first_name"
                label="First Name"
                placeholder="John"
                required
                autofocus
                :error="$errors->first('first_name')"
            />

            <x-ui.input
                wire:model="last_name"
                type="text"
                name="last_name"
                label="Last Name"
                placeholder="Doe"
                required
                :error="$errors->first('last_name')"
            />
        </div>

        <!-- Email -->
        <x-ui.input
            wire:model="email"
            type="email"
            name="email"
            label="Email"
            placeholder="you@example.com"
            required
            :error="$errors->first('email')"
        />

        <!-- Password -->
        <x-ui.input
            wire:model="password"
            type="password"
            name="password"
            label="Password"
            placeholder="••••••••"
            required
            :error="$errors->first('password')"
            help="Must be at least 8 characters"
        />

        <!-- Confirm Password -->
        <x-ui.input
            wire:model="password_confirmation"
            type="password"
            name="password_confirmation"
            label="Confirm Password"
            placeholder="••••••••"
            required
            :error="$errors->first('password_confirmation')"
        />

        <!-- Submit Button -->
        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>Create Account</span>
            <span wire:loading class="flex items-center justify-center">
                <x-ui.loading size="sm" class="mr-2" />
                Creating account...
            </span>
        </x-ui.button>
    </form>

    <!-- Login Link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-nord3 dark:text-nord4">
            Already have an account?
            <a href="{{ route('login') }}" wire:navigate class="font-medium text-primary hover:text-nord7 transition-colors">
                Sign in
            </a>
        </p>
    </div>
</div>
