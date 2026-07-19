@if($day->reservation)

													<div
															class="relative flex items-start justify-between gap-2"
														>

														<a
																href="{{ route('admin.reservations.show', $day->reservation) }}"
																class="min-w-0 flex-1"
															>

															<div class="truncate text-xs font-semibold">
																{{ $day->reservation->code }}
															</div>

															<div class="truncate text-[11px] text-slate-600">
																{{ $day->reservation->guest_name }}
															</div>

															<div class="mt-1 text-[10px] font-medium text-slate-500">
																{{ $day->badgeLabel() }}
															</div>

														</a>
														
														<button
															type="button"
															@click.stop="
																openReservation =
																	openReservation === '{{ $day->reservation->id }}'
																		? null
																		: '{{ $day->reservation->id }}'
															"
															:aria-expanded="openReservation === '{{ $day->reservation->id }}'"
															aria-haspopup="menu"
															class="rounded p-1 text-slate-400 hover:bg-white hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300"
															title="Reservation actions"
															aria-label="Reservation actions"
														>
															⋮
														</button>
														
														<div
															x-cloak
															x-show="openReservation === '{{ $day->reservation->id }}'"
															@click.outside="openReservation = null"
															@keydown.escape.window="openReservation = null"
															x-transition.origin.top.right
															class="absolute right-0 top-full mt-1 z-50 w-44 origin-top-right overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg"
														>
															<a
																href="{{ route('admin.reservations.show', $day->reservation) }}"
																class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
															>
																View Reservation
															</a>

															<a
																href="{{ route('admin.reservations.edit', $day->reservation) }}"
																class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
															>
																Edit Reservation
															</a>
															
															@if (
																in_array(
																	$day->reservation->status,
																	[
																		\Domain\Reservation\Enums\ReservationStatus::Reserved,
																		\Domain\Reservation\Enums\ReservationStatus::Confirmed,
																	],
																	true,
																)
															)
																<form
																	method="POST"
																	action="{{ route('admin.reservations.check-in', $day->reservation) }}"
																>
																	@csrf

																	<button
																		type="submit"
																		class="block w-full px-3 py-2 text-left text-sm text-emerald-600 hover:bg-emerald-50"
																	>
																		Check In
																	</button>
																</form>
															@endif
															
															<div class="border-t border-slate-200"></div>
															
															<form
																method="POST"
																action="{{ route('admin.reservations.destroy', $day->reservation) }}"
																onsubmit="return confirm('Cancel this reservation?');"
															>
																@csrf
																@method('DELETE')

																<button
																	type="submit"
																	class="block w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50"
																>
																	Cancel Reservation
																</button>
															</form>
															
														</div>

													</div>
													@else
														<a
															href="{{ route('admin.units.reservations.create', [
																'unit' => $unit,
																'check_in' => $day->day->date->toDateString(),
															]) }}"
															class="block h-16 rounded hover:bg-slate-100 transition-colors"
															title="Create reservation"
														>
														</a>
												@endif