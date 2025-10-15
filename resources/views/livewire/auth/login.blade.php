<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-nord0 dark:text-nord6">Welcome back</h2>
        <p class="mt-2 text-sm text-nord3 dark:text-nord4">Sign in to your Katra account</p>
    </div>

    <!-- Success Message -->
    @if(session('status'))
        <x-ui.alert type="success" class="mb-6" dismissible>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <!-- Login Form -->
    <form wire:submit="login" class="space-y-6">
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

        <!-- Password -->
        <x-ui.input
            wire:model="password"
            type="password"
            name="password"
            label="Password"
            placeholder="••••••••"
            required
            :error="$errors->first('password')"
        />

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <x-ui.checkbox
                wire:model="remember"
                name="remember"
                label="Remember me"
            />

            <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-medium text-nord8 hover:text-nord7 transition-colors">
                Forgot password?
            </a>
        </div>

        <!-- Submit Button -->
        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading class="flex items-center justify-center">
                <x-ui.loading size="sm" class="mr-2" />
                Signing in...
            </span>
        </x-ui.button>
    </form>

    <!-- Register Link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-nord3 dark:text-nord4">
            Don't have an account?
            <a href="{{ route('register') }}" wire:navigate class="font-medium text-nord8 hover:text-nord7 transition-colors">
                Create one now
            </a>
        </p>
    </div>
</div>
