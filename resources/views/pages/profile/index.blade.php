@extends('katra::layouts.dashboard')

@section('title', 'Edit Profile')

@section('menu')

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'katra.profile') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Information
</a>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'password.reset') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Security
</a>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'password.reset') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Billing
</a>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'password.reset') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Notifications
</a>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'password.reset') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Support
</a>

<hr class="my-8 dark:border-slate-700"/>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

@endsection

@section('canvas')
content goes here.....................
@endsection