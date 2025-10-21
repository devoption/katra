<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', mobileMenuOpen: false }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
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
        <script>
            // Prevent light/dark flash before Alpine initializes
            (function() {
                try {
                    var stored = localStorage.getItem('darkMode');
                    var isDark = stored === 'true';
                    if (isDark) document.documentElement.classList.add('dark');
                } catch (e) {}
            })();
            // Keep dark class in sync across Livewire navigations
            (function(){
                function syncDark(){
                    try {
                        var isDark = localStorage.getItem('darkMode') === 'true';
                        document.documentElement.classList.toggle('dark', isDark);
                    } catch (e) {}
                }
                document.addEventListener('livewire:navigated', syncDark);
                document.addEventListener('DOMContentLoaded', syncDark);
                window.addEventListener('storage', syncDark);
            })();
        </script>

        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-nord6 dark:bg-nord0 text-nord0 dark:text-nord4 transition-colors duration-200">
        <div class="min-h-screen max-h-screen flex">
            <!-- Sidebar -->
            <aside class="hidden md:flex md:flex-col md:w-64 bg-nord5 dark:bg-nord1 border-r border-nord4 dark:border-nord2 transition-colors duration-200" x-cloak>
                <div class="flex items-center justify-between h-16 px-6 border-b border-nord4 dark:border-nord2">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <svg class="h-8" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" clip-rule="evenodd" viewBox="0 0 212.89 43.0229">
                            <path class="fill-primary" d="M62.4738 27.9944v-12.992c0-.3413.2453-.672.736-.992.4906-.32.8853-.48 1.184-.48h20.992v25.408h11.968V4.05839h-38.144c-.9814 0-1.984.20267-3.008.608-1.024.40534-1.9627.928-2.816 1.568s-1.5467 1.376-2.08 2.208c-.5334.832-.8 1.67471-.8 2.52801v20.928c0 .896.2666 1.7707.8 2.624.5333.8533 1.2266 1.6107 2.08 2.272.8533.6613 1.8026 1.184 2.848 1.568 1.0453.384 2.1013.576 3.168.576h23.296v-9.536h-18.112c-.3414 0-.7787-.16-1.312-.48-.5334-.32-.8-.6293-.8-.928Zm60.4672 2.508v8.436h-11.97c-1.026 0-2.014-.1995-2.964-.5985-.95-.399-1.777-.912-2.48-1.539-.703-.627-1.273-1.3395-1.71-2.1375-.437-.798-.655-1.615-.655-2.451V3.76939h10.659v4.104h9.12v8.43601h-9.12v12.768c0 .304.171.6175.513.9405.342.323.703.4845 1.083.4845h7.524Zm17.776-15.5v23.936h-11.968v-27.968c0-.8533.266-1.69601.8-2.52801.533-.832 1.226-1.568 2.08-2.208.853-.64 1.792-1.16266 2.816-1.568 1.024-.40533 2.026-.608 3.008-.608h17.792v9.47201h-12.608c-.299 0-.694.16-1.184.48-.491.32-.736.6507-.736.992Zm32.64 12.992v-12.992c0-.3413.245-.672.736-.992.49-.32.885-.48 1.184-.48h20.992v25.408h11.968V4.05839h-38.144c-.982 0-1.984.20267-3.008.608-1.024.40534-1.963.928-2.816 1.568-.854.64-1.547 1.376-2.08 2.208-.534.832-.8 1.67471-.8 2.52801v20.928c0 .896.266 1.7707.8 2.624.533.8533 1.226 1.6107 2.08 2.272.853.6613 1.802 1.184 2.848 1.568 1.045.384 2.101.576 3.168.576h23.296v-9.536h-18.112c-.342 0-.779-.16-1.312-.48-.534-.32-.8-.6293-.8-.928ZM4.572 4.05839H16.54V38.9384H4.572V4.05839Z"/>
                            <path class="fill-nord3 dark:fill-nord4" d="M47.073 4.05839 30.817 21.5304l16.256 17.408H32.737l-16.32-17.408 16.32-17.47201h14.336Z"/>
                        </svg>
                    </a>
                </div>

                <!-- Navigation -->
                <x-navigation />

                <!-- User Info -->
                @auth
                <div class="p-4 border-t border-nord4 dark:border-nord2">
                    <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                            <span class="text-nord6 text-sm font-medium">
                                @if(isset(auth()->user()->first_name))
                                    {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                                @else
                                    {{ substr(auth()->user()->name ?? auth()->user()->email, 0, 2) }}
                                @endif
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-nord0 dark:text-nord6 truncate">
                                @if(isset(auth()->user()->first_name))
                                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                @else
                                    {{ auth()->user()->name ?? 'User' }}
                                @endif
                            </p>
                            <p class="text-xs text-nord3 dark:text-nord4 truncate">
                                {{ auth()->user()->email }}
                            </p>
                        </div>
                    </div>
                </div>
                @endauth
            </aside>

            <!-- Main Content Area - Full Width for Chat -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Header -->
                <header class="bg-nord5 dark:bg-nord1 border-b border-nord4 dark:border-nord2 transition-colors duration-200" x-cloak>
                    <div class="flex items-center justify-between h-16 px-6">
                        <!-- Mobile menu button -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-nord0 dark:text-nord4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Search bar placeholder -->
                        <div class="hidden md:block flex-1 max-w-lg">
                            <div class="relative">
                                <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-nord4 dark:border-nord2 bg-nord6 dark:bg-nord0 text-nord0 dark:text-nord4 focus:outline-none focus:ring-2 focus:ring-primary transition-colors duration-200">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Right side actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Theme toggle -->
                            <button @click="darkMode = !darkMode" class="p-2 text-nord3 dark:text-nord4 hover:text-nord0 dark:hover:text-nord6 transition-colors duration-200">
                                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </button>

                            <!-- Notifications -->
                            <button class="p-2 text-nord3 dark:text-nord4 hover:text-nord0 dark:hover:text-nord6 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L12.828 7H4.828z" />
                                </svg>
                            </button>

                            <!-- User menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center space-x-2 p-2 text-nord3 dark:text-nord4 hover:text-nord0 dark:hover:text-nord6 transition-colors duration-200">
                                    <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center">
                                        <span class="text-nord6 text-xs font-medium">
                                            @if(isset(auth()->user()->first_name))
                                                {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                                            @else
                                                {{ substr(auth()->user()->name ?? auth()->user()->email, 0, 2) }}
                                            @endif
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Chat Content - Full Width -->
                <main class="flex-1 overflow-hidden">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-0 z-50 md:hidden" style="display: none;">
            <div class="fixed inset-0 bg-nord0 bg-opacity-50" @click="mobileMenuOpen = false"></div>
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-nord5 dark:bg-nord1">
                <!-- Mobile sidebar content would go here -->
            </div>
        </div>

        @livewireScripts
    </body>
</html>
