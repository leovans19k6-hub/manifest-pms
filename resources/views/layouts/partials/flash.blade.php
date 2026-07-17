@if (session('status'))
    <div
        class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700"
    >
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div
        class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4"
    >
        <div class="mb-2 font-semibold text-red-700">
            Please fix the following errors:
        </div>

        <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif