@extends('layouts.admin')
@section('title', 'Reservations')
@section('content')
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    {{ $unit->property->name }}
                </p>

                <h1 class="text-2xl font-semibold text-slate-900">
					Reservations
				</h1>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.properties.units.index', $unit->property) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                >
                    Back to Units
                </a>
				
				<a
					href="{{ route('admin.units.availability.index', $unit) }}"
					class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50"
				>
					Availability
				</a>

                @if ($abilities['create'])
                    <a
                        href="{{ route('admin.units.reservations.create', $unit) }}"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                    >
                        Create Reservation
                    </a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif
		@if ($reservations->isEmpty())

			<x-empty-state
				title="No reservations yet"
				description="Create the first reservation for this unit."
			>
				@if ($abilities['create'])
					<x-button
						:href="route('admin.units.reservations.create', $unit)"
					>
						Create Reservation
					</x-button>
				@endif
			</x-empty-state>

		@else
		<x-table>

				<x-slot:head>

					<tr class="hover:bg-slate-50 transition-colors">

						<th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
							Reservation
						</th>

						<th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
							Guest
						</th>

						<th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
							Stay
						</th>

						<th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
							Status
						</th>

						<th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
							Actions
						</th>

					</tr>

				</x-slot:head>

				<x-slot:body>

					@foreach ($reservations as $reservation)

						<tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">

									<div class="font-medium text-slate-900">
										{{ $reservation->code }}
									</div>

									<div class="text-sm text-slate-500">
										{{ $reservation->source->value }}
									</div>

									</td>

                                    <td class="px-6 py-4">

										<div class="font-medium text-slate-900">
											{{ $reservation->guest_name }}
										</div>

										<div class="text-sm text-slate-500">
											{{ $reservation->guest_phone }}
										</div>

									</td>

                                    <td class="px-6 py-4 text-sm text-slate-700">

										<div>
											{{ $reservation->check_in->format('d/m/Y') }}
										</div>

										<div class="text-slate-500">
											{{ $reservation->check_out->format('d/m/Y') }}
										</div>

									</td>

                                    <td class="px-6 py-4">

										<x-status-badge :status="$reservation->status" />

									</td>

                                    <td class="px-6 py-4">

										<div class="flex justify-end gap-3">

											@if ($abilities['update'])
												<a
													href="{{ route('admin.reservations.edit', $reservation) }}"
													class="text-sm font-medium text-slate-700 hover:text-slate-900"
												>
													Edit
												</a>
											@endif

											@if ($abilities['cancel'])
												<form
													method="POST"
													action="{{ route('admin.reservations.destroy', $reservation) }}"
												>
													@csrf
													@method('DELETE')

													<button
														type="submit"
														class="text-sm font-medium text-red-600 hover:text-red-700"
													>
														Cancel
													</button>
												</form>
											@endif

										</div>

									</td>
                                </tr>

					@endforeach

				</x-slot:body>

			@endif

		</x-table>
    </div>
@endsection