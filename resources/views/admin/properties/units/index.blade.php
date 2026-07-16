@extends('layouts.admin')
@section('title', 'Units')
@section('content')
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    {{ $property->name }}
                </p>

                <h1 class="text-2xl font-semibold text-slate-900">
                    Units
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.properties.edit', $property) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                >
                    Back to Property
                </a>

                @if ($abilities['create'])
                    <a
                        href="{{ route('admin.properties.units.create', $property) }}"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                    >
                        Create Unit
                    </a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @if ($units->isEmpty())
                <div class="px-6 py-12 text-center">
                    <h2 class="text-base font-semibold text-slate-900">
                        No units yet
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Create the first unit for this property.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Unit
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Occupancy
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($units as $unit)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">
                                            {{ $unit->name }}
                                        </div>

                                        <div class="text-sm text-slate-500">
                                            {{ $unit->code }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ ucfirst($unit->type->value) }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ ucfirst($unit->status->value) }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $unit->base_occupancy }}–{{ $unit->max_occupancy }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-3">
                                            @if ($abilities['update'])
                                                <a
                                                    href="{{ route('admin.units.edit', $unit) }}"
                                                    class="text-sm font-medium text-slate-700 hover:text-slate-900"
                                                >
                                                    Edit
                                                </a>
                                            @endif
											<a
												href="{{ route('admin.units.reservations.index', $unit) }}"
												class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
											>
												Reservations
											</a>
                                            @if ($abilities['archive'])
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.units.destroy', $unit) }}"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="text-sm font-medium text-red-600 hover:text-red-700"
                                                    >
                                                        Archive
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection