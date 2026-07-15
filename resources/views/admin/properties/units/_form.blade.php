@php
    $currentType = old('type', isset($unit) ? $unit->type->value : 'room');
    $currentStatus = old('status', isset($unit) ? $unit->status->value : 'draft');
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">
            Code
        </label>

        <input
            id="code"
            name="code"
            type="text"
            value="{{ old('code', $unit->code ?? '') }}"
            required
            maxlength="50"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >

        @error('code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">
            Name
        </label>

        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $unit->name ?? '') }}"
            required
            maxlength="150"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >

        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-slate-700">
            Slug
        </label>

        <input
            id="slug"
            name="slug"
            type="text"
            value="{{ old('slug', $unit->slug ?? '') }}"
            required
            maxlength="180"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >

        @error('slug')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-slate-700">
            Type
        </label>

        <select
            id="type"
            name="type"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >
            @foreach ($types as $type)
                <option
                    value="{{ $type->value }}"
                    @selected($currentType === $type->value)
                >
                    {{ ucfirst($type->value) }}
                </option>
            @endforeach
        </select>

        @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">
            Status
        </label>

        <select
            id="status"
            name="status"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >
            @foreach ($statuses as $status)
                <option
                    value="{{ $status->value }}"
                    @selected($currentStatus === $status->value)
                >
                    {{ ucfirst($status->value) }}
                </option>
            @endforeach
        </select>

        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @foreach ([
        'capacity_adults' => 'Adult Capacity',
        'capacity_children' => 'Child Capacity',
        'bedrooms' => 'Bedrooms',
        'bathrooms' => 'Bathrooms',
        'base_occupancy' => 'Base Occupancy',
        'max_occupancy' => 'Maximum Occupancy',
        'sort_order' => 'Sort Order',
    ] as $field => $label)
        <div>
            <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">
                {{ $label }}
            </label>

            <input
                id="{{ $field }}"
                name="{{ $field }}"
                type="number"
                min="0"
                value="{{ old($field, $unit->{$field} ?? match ($field) {
                    'capacity_adults' => 2,
                    'bedrooms', 'bathrooms', 'base_occupancy' => 1,
                    'max_occupancy' => 2,
                    default => 0,
                }) }}"
                required
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
            >

            @error($field)
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">
            Description
        </label>

        <textarea
            id="description"
            name="description"
            rows="5"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >{{ old('description', $unit->description ?? '') }}</textarea>

        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>