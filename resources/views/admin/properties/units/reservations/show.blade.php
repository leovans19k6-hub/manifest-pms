@extends('layouts.admin')

@section('title', 'Reservation Details')

@section('content')
<div class="space-y-6">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Reservation {{ $reservation->code }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Reservation details
            </p>
        </div>

        <x-status-badge :status="$reservation->status->value" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">

        <x-card>
            <x-slot name="title">
                Guest Information
            </x-slot>

            <dl class="space-y-4">

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Guest Name
                    </dt>

                    <dd class="mt-1 text-slate-900">
                        {{ $reservation->guest_name }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Phone
                    </dt>

                    <dd class="mt-1 text-slate-900">
                        {{ $reservation->guest_phone ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Email
                    </dt>

                    <dd class="mt-1 text-slate-900">
                        {{ $reservation->guest_email ?: '-' }}
                    </dd>
                </div>

            </dl>
        </x-card>

        <x-card>
            <x-slot name="title">
                Stay Information
            </x-slot>

            <dl class="space-y-4">

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Check In
                    </dt>

                    <dd class="mt-1">
                        {{ $reservation->check_in->format('d/m/Y') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Check Out
                    </dt>

                    <dd class="mt-1">
                        {{ $reservation->check_out->format('d/m/Y') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Adults
                    </dt>

                    <dd class="mt-1">
                        {{ $reservation->adults }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Children
                    </dt>

                    <dd class="mt-1">
                        {{ $reservation->children }}
                    </dd>
                </div>

            </dl>
        </x-card>

        <x-card>
            <x-slot name="title">
                Reservation
            </x-slot>

            <dl class="space-y-4">

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Code
                    </dt>

                    <dd class="mt-1">
                        {{ $reservation->code }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-slate-500">
                        Source
                    </dt>

                    <dd class="mt-1">
                        {{ ucfirst($reservation->source->value) }}
                    </dd>
                </div>

            </dl>
        </x-card>

        <x-card>
            <x-slot name="title">
                Notes
            </x-slot>

            <div class="whitespace-pre-line text-slate-700">
                {{ $reservation->notes ?: 'No notes.' }}
            </div>
        </x-card>

    </div>

    <x-card>
        <x-slot name="title">
            Metadata
        </x-slot>

        <dl class="grid gap-6 md:grid-cols-2">

            <div>
                <dt class="text-sm font-medium text-slate-500">
                    Created At
                </dt>

                <dd class="mt-1">
                    {{ $reservation->created_at->format('d/m/Y H:i') }}
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-slate-500">
                    Updated At
                </dt>

                <dd class="mt-1">
                    {{ $reservation->updated_at->format('d/m/Y H:i') }}
                </dd>
            </div>

        </dl>
    </x-card>

    <div class="flex justify-end gap-3">

        <a
            href="{{ url()->previous() }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
        >
            Back
        </a>

        <a
            href="{{ route('admin.reservations.edit', $reservation) }}"
            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
        >
            Edit Reservation
        </a>

    </div>

</div>
@endsection