@extends('layouts.admin')

@section('title', __('availability.title.index'))

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
                {{ __('availability.button.back_to_units') }}
            </a>

            <a
                href="{{ route('admin.units.reservations.index', $unit) }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
            >
                {{ __('availability.button.reservations') }}
            </a>

        </div>

    </div>

    @if($calendar->weeks->isEmpty())

        <x-empty-state
            title="title="{{ __('availability.empty.title') }}""
            description="description="{{ __('availability.empty.description') }}""
        />

    @else

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

			{{-- Header --}}
			<div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">

				<a
					href="{{ route('admin.units.availability.index', [
						'unit' => $unit,
						'month' => $previousMonth->format('Y-m'),
					]) }}"
					class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50"
				>
					← {{ __('availability.button.previous') }}
				</a>

				<h2 class="text-xl font-semibold">
					{{ $calendar->month->format('F Y') }}
				</h2>

				<a
					href="{{ route('admin.units.availability.index', [
						'unit' => $unit,
						'month' => $nextMonth->format('Y-m'),
					]) }}"
					class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50"
				>
					{{ __('availability.button.next') }} →
				</a>

			</div>

			<div
				x-data="{ openReservation: null }"
				class="overflow-x-auto"
			>

				<table class="w-full table-fixed border-collapse">

					<thead>

						<tr class="bg-slate-100">

							@php
							$weekdays = [
								__('availability.weekday.mon'),
								__('availability.weekday.tue'),
								__('availability.weekday.wed'),
								__('availability.weekday.thu'),
								__('availability.weekday.fri'),
								__('availability.weekday.sat'),
								__('availability.weekday.sun'),
							];
							@endphp

							@foreach ($weekdays as $label)

								<th class="border border-slate-200 py-3 text-center text-xs text-slate-600 font-semibold">

									{{ $label }}

								</th>

							@endforeach

						</tr>

					</thead>

					<tbody>

						@foreach($calendar->weeks as $week)

							<tr>

								@foreach($week->days as $day)

									@php

										$bg = match ($day->status) {

											\Domain\Availability\Enums\AvailabilityStatus::Available => 'bg-white',

											\Domain\Availability\Enums\AvailabilityStatus::Reserved => 'bg-amber-50',

											\Domain\Availability\Enums\AvailabilityStatus::CheckedIn => 'bg-emerald-50',

										};

										$text = $day->day->inCurrentMonth
											? 'text-slate-900'
											: 'text-slate-400';

									@endphp

									<td
										class="align-top border border-slate-200 p-2 {{ $bg }}"
										style="height:130px;"
									>

										<div class="flex h-full flex-col">

											<div class="flex justify-between items-start">

												<span
													@class([
														'flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold',
														'bg-blue-600 text-white' => $day->day->date->isToday(),
														'text-slate-400' => ! $day->day->inCurrentMonth && ! $day->day->date->isToday(),
														'text-slate-900' => $day->day->inCurrentMonth && ! $day->day->date->isToday(),
													])
												>
													{{ $day->day->date->day }}
												</span>

											</div>

											<div class="mt-3">

												@if($day->reservation)

													@include(
														'admin.properties.units.availability._reservation-card',
														['day' => $day]
													)

												@else

													<a
														href="{{ route('admin.units.reservations.create', [
															'unit' => $unit,
															'check_in' => $day->day->date->toDateString(),
														]) }}"
														class="block h-16 rounded hover:bg-slate-100 transition-colors"
														title="title="{{ __('availability.button.create_reservation') }}""
													>
													</a>

												@endif

											</div>

										</div>

									</td>

								@endforeach

							</tr>

						@endforeach

					</tbody>

				</table>

			</div>

		</div>

    @endif

</div>
@endsection