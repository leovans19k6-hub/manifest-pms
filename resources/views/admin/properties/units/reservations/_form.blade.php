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
			{{ __('reservation.field.code') }}
		</label>

		<x-form.input
			name="code"
			label="__('reservation.field.code')"
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
			{{ __('reservation.field.guest_name') }}
		</label>

		<x-form.input
			name="guest_name"
			label="__('reservation.field.guest_name')"
			required
			:value="$reservation->guest_name ?? ''"
		/>

		@error('guest_name')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_phone" class="block text-sm font-medium text-slate-700">
			{{ __('reservation.field.guest_phone') }}
		</label>

		<x-form.input
			name="guest_phone"
			label="__('reservation.field.guest_phone')"
			:value="$reservation->guest_phone ?? ''"
		/>

		@error('guest_phone')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

	<div>
		<label for="guest_email" class="block text-sm font-medium text-slate-700">
			{{ __('reservation.field.guest_email') }}
		</label>

		<x-form.input
			name="guest_email"
			label="__('reservation.field.guest_phone')"
			type="email"
			:value="$reservation->guest_email ?? ''"
		/>

		@error('guest_email')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>

    <div>
		<label for="status" class="block text-sm font-medium text-slate-700">
			{{ __('reservation.field.status') }}
		</label>

		<x-form.select
			name="status"
			label="__('reservation.field.status')"
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
			{{ __('reservation.field.source') }}
		</label>

		<x-form.select
			name="source"
			label="__('reservation.field.source')"
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
			{{ __('reservation.field.adults') }}
		</label>

		<x-form.input
			name="adults"
			label="__('reservation.field.adults')"
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
			{{ __('reservation.field.children') }}
		</label>

		<x-form.input
			name="children"
			label="__('reservation.field.children')"
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
			{{ __('reservation.field.check_in') }}
		</label>

		<x-form.input
			name="check_in"
			label="__('reservation.field.check_in')"
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
			{{ __('reservation.field.check_out') }}
		</label>

		<x-form.input
			name="check_out"
			label="__('reservation.field.check_out')"
			type="date"
			required
			:value="old(
				'check_out',
				isset($reservation)
					? $reservation->check_out->format('Y-m-d')
					: optional($checkOut)->format('Y-m-d')
			)"
		/>

		@error('check_out')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div class="md:col-span-2">
		<label for="notes" class="block text-sm font-medium text-slate-700">
			{{ __('reservation.field.notes') }}
		</label>

		<x-form.textarea
			name="notes"
			label="__('reservation.field.notes')"
			rows="5"
			:value="$reservation->notes ?? ''"
		/>

		@error('notes')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
		@enderror
	</div>
	<div class="md:col-span-2">
		<label class="block text-sm font-medium text-slate-700">
			{{ __('reservation.field.metadata') }}
		</label>

		<p class="mt-2 text-sm text-slate-500">
			{{ __('reservation.message.metadata_future') }}
		</p>
	</div>
	<div class="md:col-span-2 flex items-center justify-end gap-3 border-t border-slate-200 pt-6">

		<a
			href="{{ url()->previous() }}"
			class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
		>
			{{ __('reservation.button.cancel') }}
		</a>

		<button
			type="submit"
			class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"
		>
			{{ __('reservation.button.save') }}
		</button>
	</div>
</div>