@extends('layouts.admin')

@section('title', 'Create Reservation')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Create Reservation
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Create a reservation for {{ $unit->name }}.
            </p>
        </div>

        <x-card>
            <form
                method="POST"
                action="{{ route('admin.units.reservations.store', $unit) }}"
                class="space-y-8"
            >
                @csrf

                @include('admin.properties.units.reservations._form')
            </form>
        </x-card>
    </div>
@endsection