@extends('katra::layouts.dashboard')

@section('title', ucwords(\Illuminate\Support\Str::plural(config('katra.users.alias'))))

@section('menu')
<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'katra.users.index') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    {{ ucwords(\Illuminate\Support\Str::plural(config('katra.users.alias'))) }}
</a>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'password.reset') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Roles
</a>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'password.reset') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Permissions
</a>

<a class="block px-6 py-3 my-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 @if(\Route::current()->getName() == 'password.reset') bg-primary text-slate-100 hover:text-slate-800 dark:hover:text-slate-100 @endif" href="#">
    Social Adapters
</a>
@endsection

@section('canvas')
content goes here
@endsection