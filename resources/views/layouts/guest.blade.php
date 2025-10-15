<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Katra') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-nord6 dark:bg-nord0 text-nord0 dark:text-nord4 transition-colors duration-200">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <!-- Theme toggle -->
            <div class="absolute top-4 right-4">
                <button @click="darkMode = !darkMode" class="p-2 rounded-lg hover:bg-nord4 dark:hover:bg-nord2 transition-all duration-200">
                    <svg x-show="!darkMode" class="w-5 h-5 text-nord0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
            </div>

            <!-- Logo -->
            <div class="mb-8">
                <a href="/" class="flex items-center space-x-3">
                    <!-- Logo placeholder - will be replaced with SVG -->
                    <div class="w-16 h-16 bg-nord8 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-200">
                        <span class="text-nord6 font-bold text-3xl">K</span>
                    </div>
                    <span class="text-3xl font-bold text-nord0 dark:text-nord6">Katra</span>
                </a>
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md">
                <div class="bg-nord5 dark:bg-nord1 rounded-2xl shadow-xl p-8 transition-colors duration-200 border border-nord4 dark:border-nord2">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-nord3 dark:text-nord4">
                    &copy; {{ date('Y') }} Katra. AI Workflow Engine.
                </p>
            </div>
        </div>

        @livewireScripts
    </body>
</html>

