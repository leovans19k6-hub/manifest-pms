<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Đăng nhập | Manifest Stay PMS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-slate-900">
    <main class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xl font-bold shadow-xl">
                    M
                </div>

                <h1 class="text-3xl font-bold text-white">
                    Manifest Stay PMS
                </h1>

                <p class="mt-2 text-sm text-slate-400">
                    Đăng nhập vào hệ thống quản trị
                </p>
            </div>

            <div class="rounded-2xl bg-white p-8 shadow-2xl">
                <h2 class="text-xl font-bold">
                    Đăng nhập
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Nhập thông tin tài khoản để tiếp tục.
                </p>

                @if ($errors->any())
                    <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('status'))
                    <div class="mt-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium">
                            Email
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium">
                            Mật khẩu
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="rounded border-slate-300"
                        >

                        Ghi nhớ đăng nhập
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800"
                    >
                        Đăng nhập
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                Manifest Stay PMS · Property Management System
            </p>
        </div>
    </main>
</body>
</html>