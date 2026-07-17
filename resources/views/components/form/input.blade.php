@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
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

    <input
        {{ $attributes->merge([
            'id' => $name,
            'name' => $name,
            'type' => $type,
            'value' => old($name, $value),
            'class' => 'mt-1 block w-full rounded-lg border-slate-300 shadow-sm',
        ]) }}
    >

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>