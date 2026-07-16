@extends('layouts.admin')
@section('title', 'Properties')
@section('content')
<<x-page-header
    title="Properties"
    description="Manage all hotels, villas, apartments and resorts in your organization."
>
    <x-slot:actions>
        @if ($abilities['create'])
            <a href="{{ route('admin.properties.create') }}">
                <x-button>
                    + Create Property
                </x-button>
            </a>
        @endif
    </x-slot:actions>
</x-page-header>
<div class="mb-6 flex items-center justify-between">

    <div class="text-sm text-slate-500">

        Showing

        <span class="font-semibold text-slate-900">
            {{ $properties->count() }}
        </span>

        of

        <span class="font-semibold text-slate-900">
            {{ $properties->total() }}
        </span>

        properties

    </div>

</div>
{{-- Future: Skeleton loading component --}}
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
                    placeholder="Property name or code..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-slate-500 focus:outline-none"
                >

            </div>

            <div class="lg:col-span-2">

                <label
                    for="status"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >

                    <option value="">
                        All
                    </option>

                    @foreach (\Domain\Property\Enums\PropertyStatus::cases() as $status)

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
                    for="type"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Type
                </label>

                <select
                    id="type"
                    name="type"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >

                    <option value="">
                        All
                    </option>

                    @foreach (\Domain\Property\Enums\PropertyType::cases() as $type)

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
                    for="sort"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Sort By
                </label>

                <select
                    id="sort"
                    name="sort"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >

                    @foreach ([
                        'name',
                        'code',
                        'type',
                        'status',
                        'created_at',
                        'updated_at',
                    ] as $sort)

                        <option
                            value="{{ $sort }}"
                            @selected(($filters['sort'] ?? 'name') === $sort)
                        >
                            {{ ucfirst(str_replace('_', ' ', $sort)) }}
                        </option>

                    @endforeach

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
                    href="{{ route('admin.properties.index') }}"
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
@if ($properties->isEmpty())

    <x-empty-state
		title="No properties found"
		description="There are no properties matching your current filters."
	>

		@if(request()->hasAny(['search','status','type']))

			<a
				href="{{ route('admin.properties.index') }}"
			>
				<x-button variant="secondary">
					Clear Filters
				</x-button>
			</a>

		@endif

		@if($abilities['create'])

			<a
				href="{{ route('admin.properties.create') }}"
			>
				<x-button>
					+ Create Property
				</x-button>
			</a>

		@endif

	</x-empty-state>

@else

    <div class="grid gap-5">

        @foreach ($properties as $property)

            <x-card
				class="transition duration-200 hover:-translate-y-1 hover:shadow-lg"
			>

                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex-1">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-xl"
                            >
                                🏨
                            </div>

                            <div>

                                <h3 class="text-lg font-semibold text-slate-900">
                                    {{ $property->name }}
                                </h3>

                                <p class="text-sm text-slate-500">
                                    {{ $property->code }}
                                </p>

                            </div>

                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">

                            <x-badge>
                                {{ ucfirst($property->type->value) }}
                            </x-badge>

                            @php
                                $statusColor = match ($property->status->value) {
                                    'active' => 'emerald',
                                    'draft' => 'amber',
                                    'inactive' => 'red',
                                    default => 'slate',
                                };
                            @endphp

                            <x-badge :color="$statusColor">
                                {{ ucfirst($property->status->value) }}
                            </x-badge>
							<div class="mt-5 grid gap-3 text-sm text-slate-600 md:grid-cols-2 xl:grid-cols-4">

								<div>
									<div class="text-xs uppercase tracking-wide text-slate-400">
										Property Code
									</div>

									<div class="mt-1 font-medium">
										{{ $property->code }}
									</div>
								</div>

								<div>
									<div class="text-xs uppercase tracking-wide text-slate-400">
										Type
									</div>

									<div class="mt-1 font-medium">
										{{ ucfirst($property->type->value) }}
									</div>
								</div>

								<div>
									<div class="text-xs uppercase tracking-wide text-slate-400">
										Updated
									</div>

									<div class="mt-1 font-medium">
										{{ $property->updated_at?->diffForHumans() }}
									</div>
								</div>

								<div>
									<div class="text-xs uppercase tracking-wide text-slate-400">
										Organization
									</div>

									<div class="mt-1 font-medium">
										{{ $property->organization_id }}
									</div>
								</div>

							</div>

                        </div>

                    </div>

                    <div
                        class="flex w-full flex-wrap items-center justify-start gap-3 lg:w-auto lg:justify-end"
                    >
						<div class="mb-4 flex flex-wrap justify-end gap-3">

							<div class="rounded-xl bg-slate-100 px-4 py-2 text-center">

								<div class="text-xs uppercase text-slate-500">
									Units
								</div>

								<div class="text-lg font-bold">
									--
								</div>

							</div>

							<div class="rounded-xl bg-slate-100 px-4 py-2 text-center">

								<div class="text-xs uppercase text-slate-500">
									Reservations
								</div>

								<div class="text-lg font-bold">
									--
								</div>

							</div>

						</div>
                        @if ($abilities['update'])

                            <a
                                href="{{ route('admin.properties.edit', $property) }}"
                            >
                                <x-button variant="secondary">
                                    Edit
                                </x-button>
                            </a>

                        @endif
						<a
                            href="{{ route('admin.properties.units.index', $property) }}"
                        >
                            <x-button variant="secondary">
                                Units
                            </x-button>
                        </a>
						<a
                            href="{{ route('admin.properties.media.index', $property) }}"
                        >
                            <x-button variant="secondary">
                                Media
                            </x-button>
                        </a>

                        @if ($abilities['archive'])

                            <form
                                method="POST"
                                action="{{ route('admin.properties.destroy', $property) }}"
                            >
                                @csrf
                                @method('DELETE')

                                <x-button
                                    variant="danger"
                                    type="submit"
                                    onclick="return confirm('Archive this property?')"
                                >
                                    Archive
                                </x-button>

                            </form>

                        @endif

                    </div>

                </div>

            </x-card>

        @endforeach

    </div>

@endif
@if ($properties->hasPages())

    <div class="mt-8 flex justify-center">

        {{ $properties->links() }}

    </div>

@endif
@endsection