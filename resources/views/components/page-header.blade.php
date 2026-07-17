@props([
    'title',
    'description' => null,
])

<div class="mb-8 flex items-center justify-between">

    <div>

        <h1 class="text-3xl font-bold tracking-tight text-slate-900">
            {{ $title }}
        </h1>

        @if($description)

            <p class="mt-2 text-slate-500">
                {{ $description }}
            </p>

        @endif

    </div>

    <div class="flex items-center gap-3">
        {{ $actions ?? '' }}
    </div>

</div>