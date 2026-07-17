@props([
    'type' => 'success',
])

@php
$styles = match ($type) {
    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    'error'   => 'border-red-200 bg-red-50 text-red-700',
    'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
    default   => 'border-sky-200 bg-sky-50 text-sky-700',
};
@endphp

<div {{ $attributes->merge([
    'class' => "rounded-lg border px-4 py-3 text-sm {$styles}",
]) }}>
    {{ $slot }}
</div>