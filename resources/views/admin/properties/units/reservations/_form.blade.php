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

		<x-form.input
			name="code"
			label="Reservation Code"
			maxlength="50"
			required
			:value="$reservation->code ?? ''"
		/>

		@error('code')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_name" class="block text-sm font-medium text-slate-700">
			Guest Name
		</label>

		<x-form.input
			name="guest_name"
			label="Guest Name"
			required
			:value="$reservation->guest_name ?? ''"
		/>

		@error('guest_name')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_phone" class="block text-sm font-medium text-slate-700">
			Phone
		</label>

		<x-form.input
			name="guest_phone"
			label="Phone"
			:value="$reservation->guest_phone ?? ''"
		/>

		@error('guest_phone')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_email" class="block text-sm font-medium text-slate-700">
			Email
		</label>

		<x-form.input
			name="guest_email"
			label="Email"
			type="email"
			:value="$reservation->guest_email ?? ''"
		/>

		@error('guest_email')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

    <div>
		<label for="status" class="block text-sm font-medium text-slate-700">
			Status
		</label>

		<x-form.select
			name="status"
			label="Status"
		>
			@foreach ($statuses as $status)
				<option
					value="{{ $status->value }}"
					@selected($currentStatus === $status->value)
				>
					{{ ucfirst($status->value) }}
				</option>
			@endforeach
		</x-form.select>

		@error('status')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="source" class="block text-sm font-medium text-slate-700">
			Source
		</label>

		<x-form.select
			name="source"
			label="Source"
		>
			@foreach ($sources as $source)
				<option
					value="{{ $source->value }}"
					@selected($currentSource === $source->value)
				>
					{{ ucfirst($source->value) }}
				</option>
			@endforeach
		</x-form.select>

		@error('source')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="adults" class="block text-sm font-medium text-slate-700">
			Adults
		</label>

		<x-form.input
			name="adults"
			label="Adults"
			type="number"
			min="1"
			:value="$reservation->adults ?? 2"
		/>

		@error('adults')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="children" class="block text-sm font-medium text-slate-700">
			Children
		</label>

		<x-form.input
			name="children"
			label="Children"
			type="number"
			min="0"
			:value="$reservation->children ?? 0"
		/>

		@error('children')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="check_in" class="block text-sm font-medium text-slate-700">
			Check In
		</label>

		<x-form.input
			name="check_in"
			label="Check In"
			type="date"
			required
			:value="old(
				'check_in',
				isset($reservation)
					? $reservation->check_in->format('Y-m-d')
					: optional($checkIn)->format('Y-m-d')
			)"
		/>

		@error('check_in')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div>
		<label for="check_out" class="block text-sm font-medium text-slate-700">
			Check Out
		</label>

		<x-form.input
			name="check_out"
			label="Check Out"
			type="date"
			required
			:value="isset($reservation) ? $reservation->check_out->format('Y-m-d') : ''"
		/>

		@error('check_out')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div class="md:col-span-2">
		<label for="notes" class="block text-sm font-medium text-slate-700">
			Notes
		</label>

		<x-form.textarea
			name="notes"
			label="Notes"
			rows="5"
			:value="$reservation->notes ?? ''"
		/>

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
</div>