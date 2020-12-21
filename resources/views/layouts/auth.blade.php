@extends('katra::layouts.base')

@section('content')
    <div class="flex items-center justify-center w-screen h-screen">
        <div class="flex w-2/3 rounded-lg">
            <div class="flex flex-col justify-center w-1/3 px-12 rounded-l-lg shadow-md bg-primary @if(config('katra.colors.text') === 'dark') text-slate-800 @else text-slate-100 @endif">
                @yield('aside')
            </div>
            <div class="w-2/3 p-4 rounded-r-lg shadow-md text-slate-700 dark:text-slate-100 bg-slate-50 dark:bg-slate-800">
                
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ session('status') }}
                    </div>
                @endif
                
                @yield('form')
            </div>
        </div>
    </div>
@endsection