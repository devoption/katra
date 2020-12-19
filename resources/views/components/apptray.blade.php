<div id="appTray" class="absolute top-0 left-0 z-50 flex hidden w-screen h-screen overflow-y-auto">
    <div class="h-screen p-8 shadow-2xl w-96 bg-slate-50 dark:bg-slate-700">
        <h2 class="pb-12 text-xl text-bold">
            Applications
        </h2>
        <div class="flex flex-wrap">
            <a href="{{ route('katra.home') }}" class="flex flex-col items-center justify-center w-1/3 px-3 py-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <x-fas-home class="h-6" />
                Dashboard
            </a>
            <a href="{{ route('katra.users.index') }}" class="flex flex-col items-center justify-center w-1/3 px-3 py-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <x-fas-user-shield class="h-6" />
                Access
            </a>
            <a href="{{ route('katra.profile') }}" class="flex flex-col items-center justify-center w-1/3 px-3 py-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <x-fas-user-circle class="h-6" />
                Profile
            </a>
            <a href="{{ route('katra.settings') }}" class="flex flex-col items-center justify-center w-1/3 px-3 py-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <x-fas-cog class="h-6" />
                Settings
            </a>
        </div>
    </div>
    <div onclick="document.getElementById('appTray').classList.add('hidden');" class="flex-1 h-screen opacity-90 filter filter-saturate-75 bg-slate-800"></div>
</div>