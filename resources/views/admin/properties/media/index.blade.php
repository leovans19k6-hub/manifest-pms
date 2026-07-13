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

    @if(session('status'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if($abilities['assets']['view'])
        <section>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">
                        Hình ảnh & Media
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ $assets->total() }} media
                    </p>
                </div>
            </div>

            @if($abilities['assets']['create'])
                <form
                    method="POST"
                    action="{{ route('admin.properties.media.assets.store', $property) }}"
                    enctype="multipart/form-data"
                    class="mt-6 space-y-4 rounded-lg border bg-white p-4"
                >
                    @csrf

                    <h3 class="font-semibold">
                        Tải media mới
                    </h3>

                    <div>
                        <label for="kind" class="block text-sm font-medium">
                            Loại media
                        </label>

                        <select
                            id="kind"
                            name="kind"
                            class="mt-1 w-full rounded-lg border-gray-300"
                        >
                            @foreach($assetKinds as $kind)
                                <option value="{{ $kind->value }}">
                                    {{ $kind->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="file" class="block text-sm font-medium">
                            Tệp
                        </label>

                        <input
                            id="file"
                            type="file"
                            name="file"
                            required
                            class="mt-1 block w-full"
                        >
                    </div>

                    <button
                        type="submit"
                        class="rounded-lg bg-blue-700 px-4 py-2 text-white"
                    >
                        Tải lên
                    </button>
                </form>
            @endif

            @if($assets->count())
                @if($abilities['assets']['update'])
                    <form
                        method="POST"
                        action="{{ route('admin.properties.media.assets.reorder', $property) }}"
                        class="mt-6"
                    >
                        @csrf

                        <div class="space-y-4">
                            @foreach($assets as $asset)
                                <div class="rounded-lg border bg-white p-4">
                                    <input
                                        type="hidden"
                                        name="asset_ids[]"
                                        value="{{ $asset->id }}"
                                    >

                                    <div class="font-semibold">
                                        {{ $asset->original_name }}
                                    </div>

                                    <div class="text-sm text-gray-500">
                                        {{ $asset->kind->value }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button
                            type="submit"
                            class="mt-4 rounded-lg border px-4 py-2"
                        >
                            Lưu thứ tự hiện tại
                        </button>
                    </form>
                @else
                    <div class="mt-6 space-y-4">
                        @foreach($assets as $asset)
                            <div class="rounded-lg border bg-white p-4">
                                <div class="font-semibold">
                                    {{ $asset->original_name }}
                                </div>

                                <div class="text-sm text-gray-500">
                                    {{ $asset->kind->value }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 space-y-4">
                    @foreach($assets as $asset)
                        <div class="rounded-lg border bg-white p-4">
                            @if($abilities['assets']['update'])
                                <form
                                    method="POST"
                                    action="{{ route('admin.properties.media.assets.update', $asset) }}"
                                    class="space-y-2"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="metadata[caption]"
                                        value="{{ $asset->metadata['caption'] ?? '' }}"
                                    >

                                    <button
                                        type="submit"
                                        class="text-sm text-blue-700"
                                    >
                                        Cập nhật metadata
                                    </button>
                                </form>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('admin.properties.media.assets.download', $asset) }}"
                                class="mt-2"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="text-sm text-blue-700"
                                >
                                    Tải xuống
                                </button>
                            </form>

                            @if($abilities['assets']['delete'])
                                <form
                                    method="POST"
                                    action="{{ route('admin.properties.media.assets.destroy', $asset) }}"
                                    class="mt-2"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-sm text-red-700"
                                    >
                                        Xóa media
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $assets->links() }}
                </div>
            @else
                <p class="mt-6 text-gray-500">
                    Chưa có media.
                </p>
            @endif
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