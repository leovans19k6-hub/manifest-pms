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

    @if($timeline->isEmpty())

        <x-empty-state
            title="No availability"
            description="No availability information found for this unit."
        />

    @else

        <x-table>

            <x-slot:head>

                <tr>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Date
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Status
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Reservation
                    </th>

                </tr>

            </x-slot:head>

            <x-slot:body>

                @foreach($timeline as $day)

                    <tr class="hover:bg-slate-50 transition-colors">

                        <td class="px-6 py-4">

                            <div class="font-medium text-slate-900">
                                {{ $day->date->format('d/m/Y') }}
                            </div>

                            <div class="text-sm text-slate-500">
                                {{ $day->date->format('l') }}
                            </div>

                        </td>

                        <td class="px-6 py-4">

                            @if($day->isReserved())

                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                    {{ $day->badgeLabel() }}
                                </span>

                            @else

                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                                    {{ $day->badgeLabel() }}
                                </span>

                            @endif

                        </td>

                        <td class="px-6 py-4">

                            {{ $day->reservationCode ?? '-' }}

                        </td>

                    </tr>

                @endforeach

            </x-slot:body>

        </x-table>

    @endif

</div>
@endsection