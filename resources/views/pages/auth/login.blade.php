@extends('katra::layouts.auth')

@section('title', 'Sign In')

@section('aside')
    <div class="pb-12 text-center">
        <a href="/" class="text-3xl">
            @include('katra::components.logo')
        </a>
    </div>
    
    <h2 class="py-6 text-2xl">Hello, {{ ucwords(config('katra.users.alias'))}}!</h2>

    <p class="pb-6">
        Don't have an account yet? Sign up 
        and start your journey with us
    </p>

    <a class="p-3 my-6 text-center border rounded-full dark:hover:text-slate-100 dark:hover:border-transparent dark:hover:bg-slate-800 hover:bg-slate-100 hover:text-primary" href="{{ route('register') }}">Sign Up</a>
@endsection

@section('form')
    <h2 class="p-6 my-6 text-2xl text-center">
        Sign in to {{ config('app.name') }}
    </h2>

    <div class="flex justify-center">
        <a class="flex items-center justify-center w-12 h-12 mx-1 border rounded-full dark:hover:border-transparent hover:bg-primary hover:text-slate-50" href="#">
            <x-fab-digital-ocean class="h-6" />
        </a>
        <a class="flex items-center justify-center w-12 h-12 mx-1 border rounded-full dark:hover:border-transparent hover:bg-primary hover:text-slate-50" href="#">
            <x-fab-github class="h-6" />
        </a>
        <a class="flex items-center justify-center w-12 h-12 mx-1 border rounded-full dark:hover:border-transparent hover:bg-primary hover:text-slate-50" href="#">
            <x-fab-apple class="h-6" />
        </a>
        <a class="flex items-center justify-center w-12 h-12 mx-1 border rounded-full dark:hover:border-transparent hover:bg-primary hover:text-slate-50" href="#">
            <x-fab-google class="h-6" />
        </a>
        <a class="flex items-center justify-center w-12 h-12 mx-1 border rounded-full dark:hover:border-transparent hover:bg-primary hover:text-slate-50" href="#">
            <x-fab-linkedin class="h-6" />
        </a>
        <a class="flex items-center justify-center w-12 h-12 mx-1 border rounded-full dark:hover:border-transparent hover:bg-primary hover:text-slate-50" href="#">
            <x-fab-windows class="h-6" />
        </a>
        <a class="flex items-center justify-center w-12 h-12 mx-1 border rounded-full dark:hover:border-transparent hover:bg-primary hover:text-slate-50" href="#">
            <x-fab-facebook class="h-6" />
        </a>
    </div>

    <p class="py-6 text-center">or login with your email address</p>
        
    <form class="flex flex-col mb-12" method="POST" action="{{ route('login') }}">
        @csrf
        
        <input class="p-3 mx-16 my-3 rounded-md bg-slate-100 dark:bg-slate-700" type="email" name="email" :value="old('email')" required autofocus placeholder="Email Address">
        <input class="p-3 mx-16 my-3 rounded-md bg-slate-100 dark:bg-slate-700" type="password" name="password" required autocomplete="current-password" placeholder="Password">
        
        <button class="px-12 py-3 mx-16 mt-12 mb-6 border rounded-full bg-primary text-slate-50 hover:bg-slate-500 hover:text-slate-50 dark:hover:bg-slate-900 dark:border-transparent" type="submit">Sign In</button>
        <a class="text-center hover:text-primary text-slate-500" href="{{ route('password.request') }}">Forgot your password?</a>
    </form>
@endsection