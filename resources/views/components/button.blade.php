@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
$styles = [
    'primary' =>
        'bg-slate-900 text-white hover:bg-slate-800',

    'secondary' =>
        'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',

    'danger' =>
        'bg-red-600 text-white hover:bg-red-700',

    'success' =>
        'bg-emerald-600 text-white hover:bg-emerald-700',
];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition',
        $styles[$variant] ?? $styles['primary'],
    ]) }}
>
    {{ $slot }}
</button>