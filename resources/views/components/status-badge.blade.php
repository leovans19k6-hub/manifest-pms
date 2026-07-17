@props([
    'status',
])

@php
    $value = $status instanceof \BackedEnum
        ? $status->value
        : (string) $status;

    $classes = match ($value) {
        'reserved', 'confirmed' =>
            'bg-emerald-100 text-emerald-700',

        'checked_in' =>
            'bg-blue-100 text-blue-700',

        'checked_out', 'completed' =>
            'bg-slate-100 text-slate-700',

        'cancelled', 'canceled' =>
            'bg-red-100 text-red-700',

        'pending' =>
            'bg-amber-100 text-amber-700',

        default =>
            'bg-slate-100 text-slate-700',
    };

    $label = str($value)
        ->replace('_', ' ')
        ->title();
@endphp

<x-status-badge :status="$reservation->status" />