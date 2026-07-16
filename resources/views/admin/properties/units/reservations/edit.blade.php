@extends('layouts.admin')
@section('title', 'Units')
@section('content')
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-medium text-slate-500">
                {{ $unit->property->name }}
            </p>

            <h1 class="text-2xl font-semibold text-slate-900">
                Edit Unit
            </h1>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.units.update', $unit) }}"
            class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            @csrf
            @method('PUT')

            @include('admin.properties.units._form')

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                <a
                    href="{{ route('admin.properties.units.index', $unit->property_id) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                >
                    Back to Units
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection