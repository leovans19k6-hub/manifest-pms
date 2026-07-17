<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Dashboard') | Manifest Stay PMS
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

<div class="flex min-h-screen">

    @include('layouts.partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col">

        @include('layouts.partials.topbar')

        <main class="flex-1 overflow-y-auto p-8">

            @include('layouts.partials.flash')

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>