@extends('layouts.admin')

@section('title', 'Edit Reservation')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">
            Edit Reservation
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            {{ $reservation->code }}
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('admin.reservations.update', $reservation) }}"
        class="space-y-8 rounded-xl border border-slate-200 bg-white p-8 shadow-sm"
    >
        @csrf
        @method('PUT')

        @include('admin.properties.units.reservations._form')

    </form>

</div>
@endsection