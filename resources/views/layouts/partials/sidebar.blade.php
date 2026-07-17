@php
    $currentRoute = request()->route()?->getName();
@endphp

<aside class="flex h-screen w-72 flex-col border-r border-slate-200 bg-slate-900 text-slate-100">

    {{-- Logo --}}
    <div class="border-b border-slate-800 px-6 py-6">
        <a href="{{ route('dashboard') }}" class="block">
            <div class="text-xl font-bold tracking-wide">
                Manifest
            </div>

            <div class="text-sm text-slate-400">
                Stay PMS
            </div>
        </a>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-8">

        {{-- Dashboard --}}
        <div>
            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Dashboard
            </p>

            <a
                href="{{ route('dashboard') }}"
                class="flex items-center rounded-lg px-3 py-2 text-sm transition
                {{ str_starts_with($currentRoute, 'dashboard')
                    ? 'bg-slate-800 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
            >
                Dashboard
            </a>
        </div>

        {{-- Property --}}
        <div>

            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Property
            </p>

            <a
                href="{{ route('admin.properties.index') }}"
                class="flex items-center rounded-lg px-3 py-2 text-sm transition
                {{ str_starts_with($currentRoute, 'admin.properties')
                    ? 'bg-slate-800 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
            >
                Properties
            </a>

        </div>

        {{-- Inventory --}}
        <div>

            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Inventory
            </p>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Units
            </div>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Reservations
            </div>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Availability
            </div>

        </div>

        {{-- Sales --}}
        <div>

            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Sales
            </p>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Pricing
            </div>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Rate Plans
            </div>

        </div>

        {{-- CRM --}}
        <div>

            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                CRM
            </p>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Guests
            </div>

        </div>

        {{-- Operations --}}
        <div>

            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Operations
            </p>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Housekeeping
            </div>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Maintenance
            </div>

        </div>

        {{-- Finance --}}
        <div>

            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Finance
            </p>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Payments
            </div>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Reports
            </div>

        </div>

        {{-- System --}}
        <div>

            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                System
            </p>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Users
            </div>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Roles
            </div>

            <div class="rounded-lg px-3 py-2 text-sm text-slate-500">
                Organizations
            </div>

        </div>

    </nav>

    {{-- Footer --}}
    <div class="border-t border-slate-800 px-6 py-4">
        <div class="text-sm font-medium">
            Manifest Stay PMS
        </div>

        <div class="text-xs text-slate-500">
            v0.10 Alpha
        </div>
    </div>

</aside>