@if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    @foreach ([
        ['code', 'Mã'],
        ['name', 'Tên'],
        ['slug', 'Slug'],
    ] as [$field, $label])
        <label class="block">
            <span class="mb-1 block text-sm font-medium">
                {{ $label }}
            </span>

            <input
                name="{{ $field }}"
                value="{{ old($field, $property->{$field} ?? '') }}"
                class="w-full rounded-lg border px-3 py-2"
            >

            @error($field)
                <span class="text-sm text-red-600">
                    {{ $message }}
                </span>
            @enderror
        </label>
    @endforeach

    <label class="block">
        <span class="mb-1 block text-sm font-medium">
            Múi giờ
        </span>

        <select
            name="timezone"
            class="w-full rounded-lg border px-3 py-2"
        >
            <option
                value="Asia/Ho_Chi_Minh"
                @selected(old('timezone', $property->timezone ?? 'Asia/Ho_Chi_Minh') === 'Asia/Ho_Chi_Minh')
            >
                Việt Nam (GMT+7)
            </option>
        </select>

        @error('timezone')
            <span class="text-sm text-red-600">
                {{ $message }}
            </span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">
            Tiền tệ
        </span>

        <select
            name="currency"
            class="w-full rounded-lg border px-3 py-2"
        >
            <option
                value="VND"
                @selected(old('currency', $property->currency ?? 'VND') === 'VND')
            >
                VND — Việt Nam Đồng
            </option>
        </select>

        @error('currency')
            <span class="text-sm text-red-600">
                {{ $message }}
            </span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">
            Loại
        </span>

        <select
            name="type"
            class="w-full rounded-lg border px-3 py-2"
        >
            @foreach ($types as $v)
                <option
                    value="{{ $v->value }}"
                    @selected(old('type', isset($property) ? $property->type->value : '') === $v->value)
                >
                    {{ $v->value }}
                </option>
            @endforeach
        </select>

        @error('type')
            <span class="text-sm text-red-600">
                {{ $message }}
            </span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">
            Trạng thái
        </span>

        <select
            name="status"
            class="w-full rounded-lg border px-3 py-2"
        >
            @foreach ($statuses as $v)
                <option
                    value="{{ $v->value }}"
                    @selected(old('status', isset($property) ? $property->status->value : '') === $v->value)
                >
                    {{ $v->value }}
                </option>
            @endforeach
        </select>

        @error('status')
            <span class="text-sm text-red-600">
                {{ $message }}
            </span>
        @enderror
    </label>

    <label class="block md:col-span-2">
        <span class="mb-1 block text-sm font-medium">
            Địa chỉ
        </span>

        <textarea
            name="address"
            class="w-full rounded-lg border px-3 py-2"
        >{{ old('address', $property->address ?? '') }}</textarea>

        @error('address')
            <span class="text-sm text-red-600">
                {{ $message }}
            </span>
        @enderror
    </label>
</div>

<div class="mt-6 flex gap-3">
    <button
        type="submit"
        class="rounded-lg bg-slate-900 px-4 py-2 text-white"
    >
        Lưu
    </button>

    <a
        href="{{ route('admin.properties.index') }}"
        class="rounded-lg border px-4 py-2"
    >
        Hủy
    </a>
</div>