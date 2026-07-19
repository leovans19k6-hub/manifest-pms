@extends('layouts.admin')

@section('title', __('reservation.title.create'))

@section('content')
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                {{ __('reservation.title.create') }}
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                {{ __('reservation.message.create_for_unit', ['unit' => $unit->name]) }}
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