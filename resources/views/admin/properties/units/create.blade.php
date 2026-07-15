@extends('layouts.admin')
@section('title', 'Units')
@section('content')
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-medium text-slate-500">
                {{ $property->name }}
            </p>

            <h1 class="text-2xl font-semibold text-slate-900">
                Create Unit
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route('admin.properties.units.store', $property) }}"
            class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            @csrf

            @include('admin.properties.units._form')

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                <a
                    href="{{ route('admin.properties.units.index', $property) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Create Unit
                </button>
            </div>
        </form>
    </div>
@endsection