@extends('layouts.admin')

@section('title', 'Media & Documents')

@section('content')
<div>
    <div class="mb-6">
        <a
            href="{{ route('admin.properties.edit', $property) }}"
            class="text-sm text-blue-700"
        >
            Quay lại cơ sở lưu trú
        </a>

        <h1 class="mt-2 text-2xl font-bold">
            Media & Documents
        </h1>

        <p class="text-gray-500">
            {{ $property->name }}
        </p>
    </div>

    @if($abilities['assets']['view'])
        <section>
            <h2 class="text-xl font-semibold">
                Hình ảnh & Media
            </h2>

            <p>
                {{ $assets->total() }} media
            </p>
        </section>
    @endif

    @if($abilities['documents']['view'])
        <section class="mt-8">
            <h2 class="text-xl font-semibold">
                Tài liệu
            </h2>

            <p>
                {{ $documents->total() }} tài liệu
            </p>
        </section>
    @endif
</div>
@endsection