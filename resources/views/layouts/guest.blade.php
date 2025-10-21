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
                    <svg class="h-16" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" clip-rule="evenodd" viewBox="0 0 212.89 43.0229">
                        <path class="fill-primary" d="M62.4738 27.9944v-12.992c0-.3413.2453-.672.736-.992.4906-.32.8853-.48 1.184-.48h20.992v25.408h11.968V4.05839h-38.144c-.9814 0-1.984.20267-3.008.608-1.024.40534-1.9627.928-2.816 1.568s-1.5467 1.376-2.08 2.208c-.5334.832-.8 1.67471-.8 2.52801v20.928c0 .896.2666 1.7707.8 2.624.5333.8533 1.2266 1.6107 2.08 2.272.8533.6613 1.8026 1.184 2.848 1.568 1.0453.384 2.1013.576 3.168.576h23.296v-9.536h-18.112c-.3414 0-.7787-.16-1.312-.48-.5334-.32-.8-.6293-.8-.928Zm60.4672 2.508v8.436h-11.97c-1.026 0-2.014-.1995-2.964-.5985-.95-.399-1.777-.912-2.48-1.539-.703-.627-1.273-1.3395-1.71-2.1375-.437-.798-.655-1.615-.655-2.451V3.76939h10.659v4.104h9.12v8.43601h-9.12v12.768c0 .304.171.6175.513.9405.342.323.703.4845 1.083.4845h7.524Zm17.776-15.5v23.936h-11.968v-27.968c0-.8533.266-1.69601.8-2.52801.533-.832 1.226-1.568 2.08-2.208.853-.64 1.792-1.16266 2.816-1.568 1.024-.40533 2.026-.608 3.008-.608h17.792v9.47201h-12.608c-.299 0-.694.16-1.184.48-.491.32-.736.6507-.736.992Zm32.64 12.992v-12.992c0-.3413.245-.672.736-.992.49-.32.885-.48 1.184-.48h20.992v25.408h11.968V4.05839h-38.144c-.982 0-1.984.20267-3.008.608-1.024.40534-1.963.928-2.816 1.568-.854.64-1.547 1.376-2.08 2.208-.534.832-.8 1.67471-.8 2.52801v20.928c0 .896.266 1.7707.8 2.624.533.8533 1.226 1.6107 2.08 2.272.853.6613 1.802 1.184 2.848 1.568 1.045.384 2.101.576 3.168.576h23.296v-9.536h-18.112c-.342 0-.779-.16-1.312-.48-.534-.32-.8-.6293-.8-.928ZM4.572 4.05839H16.54V38.9384H4.572V4.05839Z"/>
                        <path class="fill-nord3 dark:fill-nord4" d="M47.073 4.05839 30.817 21.5304l16.256 17.408H32.737l-16.32-17.408 16.32-17.47201h14.336Z"/>
                    </svg>
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

