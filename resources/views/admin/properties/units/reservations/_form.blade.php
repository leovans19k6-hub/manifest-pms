@php
    $currentStatus = old(
        'status',
        isset($reservation)
            ? $reservation->status->value
            : 'reserved',
    );

    $currentSource = old(
        'source',
        isset($reservation)
            ? $reservation->source->value
            : 'manual',
    );
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
		<label for="code" class="block text-sm font-medium text-slate-700">
			Reservation Code
		</label>

		<input
			id="code"
			name="code"
			type="text"
			maxlength="50"
			required
			value="{{ old('code', $reservation->code ?? '') }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('code')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_name" class="block text-sm font-medium text-slate-700">
			Guest Name
		</label>

		<input
			id="guest_name"
			name="guest_name"
			type="text"
			required
			value="{{ old('guest_name', $reservation->guest_name ?? '') }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('guest_name')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_phone" class="block text-sm font-medium text-slate-700">
			Phone
		</label>

		<input
			id="guest_phone"
			name="guest_phone"
			type="text"
			value="{{ old('guest_phone', $reservation->guest_phone ?? '') }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('guest_phone')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_email" class="block text-sm font-medium text-slate-700">
			Email
		</label>

		<input
			id="guest_email"
			name="guest_email"
			type="email"
			value="{{ old('guest_email', $reservation->guest_email ?? '') }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('guest_email')
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
	<div>
		<label for="source" class="block text-sm font-medium text-slate-700">
			Source
		</label>

		<select
			id="source"
			name="source"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>
			@foreach ($sources as $source)
				<option
					value="{{ $source->value }}"
					@selected($currentSource === $source->value)
				>
					{{ ucfirst($source->value) }}
				</option>
			@endforeach
		</select>

		@error('source')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="adults" class="block text-sm font-medium text-slate-700">
			Adults
		</label>

		<input
			id="adults"
			name="adults"
			type="number"
			min="1"
			value="{{ old('adults', $reservation->adults ?? 2) }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('adults')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="children" class="block text-sm font-medium text-slate-700">
			Children
		</label>

		<input
			id="children"
			name="children"
			type="number"
			min="0"
			value="{{ old('children', $reservation->children ?? 0) }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('children')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="check_in" class="block text-sm font-medium text-slate-700">
			Check In
		</label>

		<input
			id="check_in"
			name="check_in"
			type="date"
			required
			value="{{ old('check_in', isset($reservation) ? $reservation->check_in->format('Y-m-d') : '') }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('check_in')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="check_out" class="block text-sm font-medium text-slate-700">
			Check Out
		</label>

		<input
			id="check_out"
			name="check_out"
			type="date"
			required
			value="{{ old('check_out', isset($reservation) ? $reservation->check_out->format('Y-m-d') : '') }}"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>

		@error('check_out')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div class="md:col-span-2">
		<label for="notes" class="block text-sm font-medium text-slate-700">
			Notes
		</label>

		<textarea
			id="notes"
			name="notes"
			rows="5"
			class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
		>{{ old('notes', $reservation->notes ?? '') }}</textarea>

		@error('notes')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div class="md:col-span-2">
		<label class="block text-sm font-medium text-slate-700">
			Metadata
		</label>

		<p class="mt-2 text-sm text-slate-500">
			Metadata editing will be available in a future sprint.
		</p>
	</div>
	<div class="md:col-span-2 flex items-center justify-end gap-3 border-t border-slate-200 pt-6">

		<a
			href="{{ url()->previous() }}"
			class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
		>
			Cancel
		</a>

		<button
			type="submit"
			class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"
		>
			Save Reservation
		</button>
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