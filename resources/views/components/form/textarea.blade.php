@props([
    'name',
    'label' => null,
    'rows' => 5,
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

    <textarea
        {{ $attributes->merge([
            'id' => $name,
            'name' => $name,
            'rows' => $rows,
            'class' => 'mt-1 block w-full rounded-lg border-slate-300 shadow-sm',
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>