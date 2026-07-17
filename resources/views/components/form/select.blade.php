@props([
    'name',
    'label' => null,
])

<div>
    @if ($label)
        <label
            for="{{ $name }}"
            class="block text-sm font-medium text-slate-700"
        >
            {{ $label }}
        </label>
    @endif

    <select
        {{ $attributes->merge([
            'id' => $name,
            'name' => $name,
            'class' => 'mt-1 block w-full rounded-lg border-slate-300 shadow-sm',
        ]) }}
    >
        {{ $slot }}
    </select>

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>