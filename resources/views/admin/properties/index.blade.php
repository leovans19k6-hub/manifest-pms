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
<form method="GET" class="mb-6 grid gap-3 rounded-xl bg-white p-4 shadow-sm md:grid-cols-6">
<input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên hoặc mã" class="rounded-lg border px-3 py-2 md:col-span-2">
<select name="status" class="rounded-lg border px-3 py-2"><option value="">Mọi trạng thái</option>@foreach(\Domain\Property\Enums\PropertyStatus::cases() as $v)<option value="{{ $v->value }}" @selected(($filters['status']??'')===$v->value)>{{ $v->value }}</option>@endforeach</select>
<select name="type" class="rounded-lg border px-3 py-2"><option value="">Mọi loại</option>@foreach(\Domain\Property\Enums\PropertyType::cases() as $v)<option value="{{ $v->value }}" @selected(($filters['type']??'')===$v->value)>{{ $v->value }}</option>@endforeach</select>
<select name="sort" class="rounded-lg border px-3 py-2">@foreach(['name','code','type','status','created_at','updated_at'] as $v)<option value="{{ $v }}" @selected(($filters['sort']??'name')===$v)>{{ $v }}</option>@endforeach</select>
<button class="rounded-lg bg-gray-800 px-4 py-2 text-white">Lọc</button></form>
<div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left"><thead class="bg-gray-100 text-sm"><tr><th class="p-3">Mã</th><th class="p-3">Tên</th><th class="p-3">Loại</th><th class="p-3">Trạng thái</th><th class="p-3">Thao tác</th></tr></thead><tbody>
@forelse($properties as $property)<tr class="border-t"><td class="p-3">{{ $property->code }}</td><td class="p-3 font-medium">{{ $property->name }}</td><td class="p-3">{{ $property->type->value }}</td><td class="p-3">{{ $property->status->value }}</td><td class="p-3"><div class="flex gap-3">@if($abilities['update'])<a class="text-blue-700" href="{{ route('admin.properties.edit',$property) }}">Sửa</a>@endif @if($abilities['archive'])<form method="POST" action="{{ route('admin.properties.destroy',$property) }}">@csrf @method('DELETE')<button class="text-red-700" onclick="return confirm('Lưu trữ cơ sở này?')">Lưu trữ</button></form>@endif</div></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">Chưa có dữ liệu.</td></tr>@endforelse
</tbody></table></div><div class="mt-5">{{ $properties->links() }}</div>
@endsection