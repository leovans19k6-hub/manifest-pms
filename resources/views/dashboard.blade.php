@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-8">

    <div class="flex items-center justify-between">

        <x-page-header
		title="Tổng quan hệ thống"
		:description="'Chào mừng trở lại, ' . auth()->user()?->name"
		>

		<x-slot:actions>

			<x-button variant="secondary">
				+ Property
			</x-button>

			<x-button>
				+ Reservation
			</x-button>

		</x-slot:actions>

	</x-page-header>

    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

        <x-stats-card
            title="Occupancy"
            value="78%"
            change="+2.5%"
            icon="🏨"
        />

        <x-stats-card
            title="Revenue"
            value="4.2"
            suffix="Tỷ"
            icon="💰"
        />

        <x-stats-card
            title="Reservations"
            value="126"
            change="+18"
            icon="📅"
        />

        <x-stats-card
            title="Available Units"
            value="42"
            icon="🛏"
        />

    </div>

    <div class="grid gap-6 lg:grid-cols-3">

			<x-card class="lg:col-span-2">

				<x-section-title
					title="Recent Activity"
				/>

				<div class="space-y-4">

					<div class="flex items-center justify-between">

						<div>

							<div class="font-medium">
								Reservation RES-00025 created
							</div>

							<div class="text-sm text-slate-500">
								5 minutes ago
							</div>

						</div>

						<x-badge color="emerald">
							New
						</x-badge>

					</div>

					<div class="flex items-center justify-between">

						<div>

							<div class="font-medium">
								Villa Harbor updated
							</div>

							<div class="text-sm text-slate-500">
								20 minutes ago
							</div>

						</div>

						<x-badge color="blue">
							Update
						</x-badge>

					</div>

					<div class="flex items-center justify-between">

						<div>

							<div class="font-medium">
								Unit A-120 archived
							</div>

							<div class="text-sm text-slate-500">
								1 hour ago
							</div>

						</div>

						<x-badge color="amber">
							Archive
						</x-badge>

					</div>

				</div>

			</x-card>

			<x-card>

		<x-section-title
			title="Quick Actions"
		/>

		<div class="grid gap-3">

			<x-button
				variant="secondary"
				class="justify-start"
			>
				➕ New Property
			</x-button>

			<x-button
				variant="secondary"
				class="justify-start"
			>
				🛏 New Unit
			</x-button>

			<x-button
				variant="secondary"
				class="justify-start"
			>
				📅 New Reservation
			</x-button>

			<x-button
				variant="secondary"
				class="justify-start"
			>
				📊 View Reports
			</x-button>

		</div>

	</x-card>

    </div>

</div>

@endsection