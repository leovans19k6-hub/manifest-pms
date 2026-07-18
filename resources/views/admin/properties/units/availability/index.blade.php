@extends('layouts.admin')

@section('title', 'Availability')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <p class="text-sm font-medium text-slate-500">
                {{ $unit->property->name }}
            </p>

            <h1 class="text-2xl font-semibold text-slate-900">
                Availability
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                {{ $unit->name }}
            </p>
        </div>

        <div class="flex items-center gap-3">

            <a
                href="{{ route('admin.properties.units.index', $unit->property) }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
            >
                Back to Units
            </a>

            <a
                href="{{ route('admin.units.reservations.index', $unit) }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
            >
                Reservations
            </a>

        </div>

    </div>

    @if($calendar->weeks->isEmpty())

        <x-empty-state
            title="No availability"
            description="No availability information found for this unit."
        />

    @else

        <div class="rounded-lg border border-slate-200 bg-white p-6">

			<h2 class="text-lg font-semibold text-slate-900">
				{{ $month->format('F Y') }}
			</h2>

			<p class="mt-2 text-sm text-slate-500">
				Calendar rendering will be added in the next commit.
			</p>

		</div>

    @endif

</div>
@endsection