@props([
    'title' => '',
    'weeks' => [],
])

<x-card>

    @if($title)
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">
                {{ $title }}
            </h2>
        </div>
    @endif

    <div class="grid grid-cols-7 border-b border-slate-200">

        @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)

            <div class="border-r border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 last:border-r-0">
                {{ $day }}
            </div>

        @endforeach

    </div>

    @foreach($weeks as $week)

        <div class="grid grid-cols-7">

            @foreach($week as $day)

                <div
                    class="aspect-square border-r border-b border-slate-200 p-2 last:border-r-0 transition hover:bg-slate-50"
                >

                    <div class="text-sm font-medium text-slate-700">

                        {{ $day['day'] }}

                    </div>

                </div>

            @endforeach

        </div>

    @endforeach

</x-card>