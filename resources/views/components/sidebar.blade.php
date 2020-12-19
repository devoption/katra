<div class="h-screen shadow-xl w-72 bg-slate-50 dark:bg-slate-800">
<h2 class="flex flex-col justify-center h-16 px-6 border-b dark:border-slate-700">
    <a class="font-bold hover:text-primary" href="/">{{ config('app.name') }}</a>
    <small>@yield('title')</small>
</h2>
<div class="p-6">
    @yield('menu')
</div>
</div>