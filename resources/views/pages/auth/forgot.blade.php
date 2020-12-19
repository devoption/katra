@extends('katra::layouts.auth')

@section('title', 'Forgot Password')

@section('aside')
    <div class="pb-12 text-center">
        <a href="/" class="text-3xl">
            @include('katra::components.logo')
        </a>
    </div>
    
    <h2 class="py-6 text-2xl">Hello, {{ ucwords(config('katra.users.alias'))}}!</h2>

    <p class="pb-6">
        Remember your password and want to try to sign in again?
    </p>

    <a class="p-3 my-6 text-center border rounded-full dark:hover:text-slate-100 dark:hover:border-transparent dark:hover:bg-slate-800 hover:bg-slate-100 hover:text-primary" href="{{ route('login') }}">Sign In</a>
@endsection

@section('form')
    <h2 class="p-6 pt-6 text-2xl text-center">
        Reset password for {{ config('app.name') }}
    </h2>

    <p class="py-6 text-center">Enter the email address you used to sign up for {{ config('app.name') }}</p>
    
    <form class="flex flex-col mb-12">
        <input class="p-3 mx-16 my-3 rounded-md bg-slate-100 dark:bg-slate-700" type="text" name="email">

        <button class="px-12 py-3 mx-16 mt-12 border rounded-full dark:hover:bg-slate-900 dark:border-transparent bg-primary text-slate-50 hover:bg-slate-500 hover:text-slate-50" type="submit">Request Password Reset</button>
    </form>
@endsection