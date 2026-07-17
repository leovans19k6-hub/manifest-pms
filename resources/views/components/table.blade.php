<div
    {{ $attributes->class([
        'overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm',
    ]) }}
>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            @isset($head)
                <thead class="bg-slate-50">
                    {{ $head }}
                </thead>
            @endisset

            <tbody class="divide-y divide-slate-100 bg-white">
                {{ $body ?? $slot }}
            </tbody>
        </table>
    </div>
</div>