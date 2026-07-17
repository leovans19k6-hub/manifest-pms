<header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-8">

    <div>
        <h1 class="text-xl font-semibold text-slate-900">
            @yield('title', 'Dashboard')
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Manifest Stay PMS Administration
        </p>
    </div>

    <div class="flex items-center gap-6">

        <button
            type="button"
            class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-100"
        >
            🔔
        </button>

        <div class="text-right">
            <div class="text-sm font-medium text-slate-900">
                {{ auth()->user()?->name }}
            </div>

            <div class="text-xs text-slate-500">
                {{ auth()->user()?->email }}
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('logout') }}"
        >
            @csrf

            <button
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
            >
                Logout
            </button>
        </form>

    </div>

</header>