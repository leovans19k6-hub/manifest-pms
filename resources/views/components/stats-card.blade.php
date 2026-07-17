@props([
    'title',
    'value',
    'suffix' => '',
    'change' => null,
    'icon' => '📊',
])

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md">

    <div class="flex items-start justify-between">

        <div>

            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                {{ $title }}
            </p>

            <div class="mt-5 flex items-end gap-2">

                <span class="text-4xl font-bold text-slate-900">
                    {{ $value }}
                </span>

                @if($suffix)
                    <span class="pb-1 text-sm text-slate-400">
                        {{ $suffix }}
                    </span>
                @endif

            </div>

            @if($change)

                <div class="mt-3 text-sm font-semibold text-emerald-600">
                    {{ $change }}
                </div>

            @endif

        </div>

        <div
            class="flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-2xl"
        >
            {{ $icon }}
        </div>

    </div>

</div>