@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Tổng quan hệ thống
        </h1>

        <p class="text-gray-500">
            Chào mừng trở lại, {{ auth()->user()->name }}
        </p>
    </header>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">
                    Công suất phòng (Occupancy)
                </h3>

                <span class="rounded-lg bg-blue-50 p-2 text-blue-500">
                    📊
                </span>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-gray-800">
                    78%
                </span>

                <span class="text-sm font-medium text-green-500">
                    +2.5%
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">
                    Doanh thu (Revenue)
                </h3>

                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-500">
                    💰
                </span>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-gray-800">
                    4.2 tỷ
                </span>

                <span class="text-sm text-gray-400">
                    VND
                </span>
            </div>
        </div>
    </div>
@endsection