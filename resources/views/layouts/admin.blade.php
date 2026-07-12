<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Quản trị') | Manifest Stay PMS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
<div class="min-h-screen md:flex">
    <aside class="flex w-full flex-col bg-slate-900 p-5 text-white md:min-h-screen md:w-64">
        <a
            href="{{ route('dashboard') }}"
            class="text-xl font-bold"
        >
            Manifest Stay PMS
        </a>

        <nav class="mt-8 space-y-2">
            <a
                href="{{ route('dashboard') }}"
                class="block rounded-lg px-3 py-2 hover:bg-slate-800"
            >
                Tổng quan
            </a>

            @auth
                <a
                    href="{{ route('admin.properties.index') }}"
                    class="block rounded-lg px-3 py-2 hover:bg-slate-800"
                >
                    Cơ sở lưu trú
                </a>
            @endauth
        </nav>

        @auth
            <div class="mt-auto border-t border-slate-700 pt-4">
                <div class="mb-3 px-3">
                    <p class="text-sm font-medium text-white">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-slate-400">
                        {{ auth()->user()->email }}
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-200 transition hover:bg-slate-800 hover:text-white"
                    >
                        Đăng xuất
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <main class="flex-1 p-4 md:p-8">
        @if (session('status'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>