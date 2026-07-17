@extends('layouts.admin')
@section('title', 'Units')
@section('content')
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header
			title="Units"
			:description="'Manage rooms, villas and inventory for ' . $property->name"
		>
			<div class="flex items-center justify-between">

				<div class="text-sm text-slate-500">

					Showing

					<span class="font-semibold">
						{{ $units->count() }}
					</span>

					units

				</div>

			</div>
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
			<x-card class="mb-6">
				<form method="GET">
					<div class="grid gap-4 lg:grid-cols-12">

						<div class="lg:col-span-4">
							<input
								name="search"
								value="{{ request('search') }}"
								placeholder="Search unit..."
								class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
							>
						</div>

						<div class="lg:col-span-2">
							<select
								name="status"
								class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
							>
								<option value="">All Status</option>

								@foreach ($statuses as $status)
									<option
										value="{{ $status->value }}"
										@selected(request('status') === $status->value)
									>
										{{ ucfirst($status->value) }}
									</option>
								@endforeach
							</select>
						</div>

						<div class="lg:col-span-2">
							<select
								name="type"
								class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
							>
								<option value="">All Type</option>

								@foreach ($types as $type)
									<option
										value="{{ $type->value }}"
										@selected(request('type') === $type->value)
									>
										{{ ucfirst($type->value) }}
									</option>
								@endforeach
							</select>
						</div>

						<div class="lg:col-span-2">
							<button
								class="w-full rounded-xl bg-slate-900 py-2.5 text-white"
							>
								Filter
							</button>
						</div>

						<div class="lg:col-span-2">
							<a
								href="{{ route('admin.properties.units.index', $property) }}"
								class="block w-full rounded-xl border border-slate-300 py-2.5 text-center"
							>
								Reset
							</a>
						</div>

					</div>
				</form>
			</x-card>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @if ($units->isEmpty())
                <x-empty-state
					title="No units found"
					description="Create your first unit for this property."
				>
					@if ($abilities['create'])
						<a href="{{ route('admin.properties.units.create', $property) }}">
							<x-button>
								+ Create Unit
							</x-button>
						</a>
					@endif
				</x-empty-state>
            @else
                <div class="overflow-x-auto">
					{{-- Sprint 010: Availability Calendar column will be inserted here.--}}
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
								{{-- Availability Calendar Column (Sprint 010) --}}
								<th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
									Updated
								</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($units as $unit)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">
                                            {{ $unit->name }}
                                        </div>

                                        <div class="text-sm text-slate-500">
                                            {{ $unit->code }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
										<x-badge color="slate">
											{{ ucfirst($unit->type->value) }}
										</x-badge>
									</td>

                                    <td class="px-6 py-4">
										@php
											$statusColor = match ($unit->status->value) {
												'available', 'active' => 'emerald',
												'occupied' => 'blue',
												'cleaning' => 'amber',
												'maintenance', 'inactive' => 'red',
												default => 'slate',
											};
										@endphp

										<x-badge :color="$statusColor">
											{{ ucfirst($unit->status->value) }}
										</x-badge>
									</td>

                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        <span class="font-medium">
											{{ $unit->base_occupancy }}
										</span>

										<span class="text-slate-400">
											/
										</span>

										<span class="text-slate-600">
											{{ $unit->max_occupancy }}
										</span>
                                    </td>
									<td class="px-6 py-4 text-sm text-slate-500">
										{{ $unit->updated_at?->diffForHumans() }}
									</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap justify-end gap-2 whitespace-nowrap">
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
													onsubmit="return confirm('Archive this unit?')"
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
					@if($units instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)

						<div class="border-t bg-white px-6 py-4">

							{{ $units->links() }}

						</div>

					@endif
                </div>
            @endif
        </div>
    </div>
@endsection