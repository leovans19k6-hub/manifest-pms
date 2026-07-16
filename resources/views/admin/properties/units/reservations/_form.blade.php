@php
    use Domain\Reservation\Enums\ReservationSource;
    use Domain\Reservation\Enums\ReservationStatus;

    $currentStatus = old(
        'status',
        isset($reservation)
            ? $reservation->status->value
            : ReservationStatus::Reserved->value,
    );

    $currentSource = old(
        'source',
        isset($reservation)
            ? $reservation->source->value
            : ReservationSource::Website->value,
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
            required
            maxlength="50"
            value="{{ old('code', $reservation->code ?? '') }}"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >

        @error('code')
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
            @foreach (ReservationStatus::cases() as $status)
                <option
                    value="{{ $status->value }}"
                    @selected($currentStatus === $status->value)
                >
                    {{ ucfirst($status->value) }}
                </option>
            @endforeach
        </select>
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
            @foreach (ReservationSource::cases() as $source)
                <option
                    value="{{ $source->value }}"
                    @selected($currentSource === $source->value)
                >
                    {{ ucfirst($source->value) }}
                </option>
            @endforeach
        </select>
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
            value="{{ old('adults', $reservation->adults ?? 1) }}"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >
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
    </div>

    <div>
        <label for="check_in" class="block text-sm font-medium text-slate-700">
            Check In
        </label>

        <input
            id="check_in"
            name="check_in"
            type="datetime-local"
            value="{{ old('check_in', isset($reservation) ? $reservation->check_in->format('Y-m-d\TH:i') : '') }}"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >
    </div>

    <div>
        <label for="check_out" class="block text-sm font-medium text-slate-700">
            Check Out
        </label>

        <input
            id="check_out"
            name="check_out"
            type="datetime-local"
            value="{{ old('check_out', isset($reservation) ? $reservation->check_out->format('Y-m-d\TH:i') : '') }}"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"
        >
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
    </div>

</div>