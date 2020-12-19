<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        @yield('meta')

        <title>@yield('title') | {{ config('app.name') }}</title>

        @if(config('app.env') === 'production') 
            <link href="{{ asset('katra/css/katra.min.css') }}" rel="stylesheet">
        @else
            <link href="{{ asset('katra/css/katra.css') }}" rel="stylesheet">
        @endif

        <style>
            :root {
                --primary-color: {{ config('katra.colors.primary', '#6201EE') }};
            }
        </style>
        
        @livewireStyles
        
        @yield('css')
    </head>
    <body class="text-slate-900 bg-slate-100 dark:bg-slate-900 dark:text-slate-100">
        @yield('content')

        @livewireScripts
        
        @yield('js')
    </body>
</html>