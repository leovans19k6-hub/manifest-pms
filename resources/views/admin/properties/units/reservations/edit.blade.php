@extends('layouts.admin')

@section('title', 'Edit Reservation')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Edit Reservation
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                {{ $reservation->code }}
            </p>
        </div>

        <x-card>
            <form
                method="POST"
                action="{{ route('admin.reservations.update', $reservation) }}"
                class="space-y-8"
            >
                @csrf
                @method('PUT')

                @include('admin.properties.units.reservations._form')
            </form>
        </x-card>
    </div>
@endsection