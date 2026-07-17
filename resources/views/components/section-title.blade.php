@props([
    'title',
])

<div class="mb-5 flex items-center justify-between">

    <h2 class="text-lg font-semibold text-slate-900">
        {{ $title }}
    </h2>

    {{ $slot }}

</div>