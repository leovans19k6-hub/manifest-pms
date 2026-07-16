@extends('layouts.admin')
@section('title', 'Units')
@section('content')
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header
			title="Units"
			:description="'Manage rooms, villas and inventory for ' . $property->name"
		>

			<x-slot:actions>

				<a
					href="{{ route('admin.properties.edit', $property) }}"
				>
					<x-button
						variant="secondary"
					>
						← Property
					</x-button>
				</a>

				@if ($abilities['create'])

					<a
						href="{{ route('admin.properties.units.create', $property) }}"
					>
						<x-button>

							+ Create Unit

						</x-button>
					</a>

				@endif

			</x-slot:actions>

		</x-page-header>

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
                                                <x-card class="mb-6">

													<form method="GET">

														<div class="grid gap-4 lg:grid-cols-12">

															<div class="lg:col-span-4">

																<label
																	for="search"
																	class="mb-2 block text-sm font-medium text-slate-700"
																>
																	Search
																</label>

																<input
																	id="search"
																	name="search"
																	type="text"
																	value="{{ $filters['search'] ?? '' }}"
																	placeholder="Search by unit name or code..."
																	class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-slate-500 focus:outline-none"
																>

															</div>

															<div class="lg:col-span-2">

																<label
																	class="mb-2 block text-sm font-medium"
																>
																	Status
																</label>

																<select
																	name="status"
																	class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
																>

																	<option value="">
																		All
																	</option>

																	@foreach($statuses as $status)

																		<option
																			value="{{ $status->value }}"
																			@selected(($filters['status'] ?? '') === $status->value)
																		>
																			{{ ucfirst($status->value) }}
																		</option>

																	@endforeach

																</select>

															</div>

															<div class="lg:col-span-2">

																<label
																	class="mb-2 block text-sm font-medium"
																>
																	Type
																</label>

																<select
																	name="type"
																	class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
																>

																	<option value="">
																		All
																	</option>

																	@foreach($types as $type)

																		<option
																			value="{{ $type->value }}"
																			@selected(($filters['type'] ?? '') === $type->value)
																		>
																			{{ ucfirst($type->value) }}
																		</option>

																	@endforeach

																</select>

															</div>

															<div class="lg:col-span-2">

																<label
																	class="mb-2 block text-sm font-medium"
																>
																	Occupancy
																</label>

																<select
																	disabled
																	class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5"
																>

																	<option>
																		Coming Soon
																	</option>

																</select>

															</div>

															<div
																class="flex items-end gap-3 lg:col-span-2"
															>

																<x-button
																	type="submit"
																	class="w-full"
																>
																	Filter
																</x-button>

																<a
																	href="{{ route('admin.properties.units.index', $property) }}"
																	class="w-full"
																>
																	<x-button
																		variant="secondary"
																		class="w-full"
																	>
																		Reset
																	</x-button>
																</a>

															</div>

														</div>

													</form>

												</x-card>
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