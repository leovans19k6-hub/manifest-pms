@props([
    'title',
    'description',
])

<x-card class="py-16 text-center">

    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-3xl">
        📂
    </div>

    <h3 class="mt-6 text-xl font-semibold">
        {{ $title }}
    </h3>

    <p class="mx-auto mt-2 max-w-md text-slate-500">
        {{ $description }}
    </p>

    @if(trim($slot))

        <div class="mt-8">
            {{ $slot }}
        </div>

    @endif

</x-card>