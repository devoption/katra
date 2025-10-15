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
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="hidden md:flex md:flex-col md:w-64 bg-nord5 dark:bg-nord1 border-r border-nord4 dark:border-nord2 transition-colors duration-200">
                <div class="flex items-center justify-between h-16 px-6 border-b border-nord4 dark:border-nord2">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <!-- Logo placeholder - will be replaced with SVG -->
                        <div class="w-8 h-8 bg-nord8 rounded-lg flex items-center justify-center">
                            <span class="text-nord6 font-bold text-lg">K</span>
                        </div>
                        <span class="text-lg font-semibold text-nord0 dark:text-nord6">Katra</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </x-slot>
                        Dashboard
                    </x-nav-link>

                    <x-nav-link href="#" :active="false">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </x-slot>
                        Chat with Katra
                    </x-nav-link>

                    <x-nav-link href="{{ route('workflows.index') }}" :active="request()->routeIs('workflows.*')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </x-slot>
                        Workflows
                    </x-nav-link>

                    <x-nav-link href="{{ route('agents.index') }}" :active="request()->routeIs('agents.*')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </x-slot>
                        Agents
                    </x-nav-link>

                    <x-nav-link href="{{ route('tools.index') }}" :active="request()->routeIs('tools.*')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </x-slot>
                        Tools
                    </x-nav-link>

                    <x-nav-link href="{{ route('contexts.index') }}" :active="request()->routeIs('contexts.*')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </x-slot>
                        Contexts
                    </x-nav-link>

                    @if(auth()->user()->isAdmin())
                        <div class="pt-4 mt-4 border-t border-nord4 dark:border-nord2">
                            <p class="px-3 text-xs font-semibold text-nord3 dark:text-nord4 uppercase tracking-wider mb-2">
                                Administration
                            </p>

                            <x-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">
                                <x-slot name="icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </x-slot>
                                Users
                            </x-nav-link>

                            <x-nav-link href="{{ route('credentials.index') }}" :active="request()->routeIs('credentials.*')">
                                <x-slot name="icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </x-slot>
                                Credentials
                            </x-nav-link>

                            <x-nav-link href="#" :active="false">
                                <x-slot name="icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </x-slot>
                                Logs
                            </x-nav-link>
                        </div>
                    @endif
                </nav>

                <!-- User menu -->
                @auth
                <div class="p-4 border-t border-nord4 dark:border-nord2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-nord8 rounded-full flex items-center justify-center">
                                <span class="text-nord6 text-sm font-medium">
                                    @if(isset(auth()->user()->first_name))
                                        {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
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
                </div>
                @endauth
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Header -->
                <header class="bg-nord5 dark:bg-nord1 border-b border-nord4 dark:border-nord2 transition-colors duration-200">
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
                                <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-nord4 dark:border-nord2 bg-nord6 dark:bg-nord0 text-nord0 dark:text-nord4 focus:outline-none focus:ring-2 focus:ring-nord8 transition-colors duration-200">
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
                            <button @click="darkMode = !darkMode" class="p-2 rounded-lg hover:bg-nord4 dark:hover:bg-nord2 transition-colors duration-200">
                                <svg x-show="!darkMode" class="w-5 h-5 text-nord0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                                <svg x-show="darkMode" class="w-5 h-5 text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </button>

                            <!-- Notifications -->
                            <button class="p-2 rounded-lg hover:bg-nord4 dark:hover:bg-nord2 transition-colors duration-200 relative">
                                <svg class="w-5 h-5 text-nord0 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-nord11 rounded-full"></span>
                            </button>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg hover:bg-nord4 dark:hover:bg-nord2 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-nord0 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-nord6 dark:bg-nord0 transition-colors duration-200">
                    <div class="container mx-auto px-6 py-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>

