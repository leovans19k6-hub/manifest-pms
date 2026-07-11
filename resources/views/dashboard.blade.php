<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Manifest Stay PMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="p-8">
        <!-- Header -->
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Tổng quan hệ thống</h1>
            <p class="text-gray-500">Chào mừng trở lại, Trung Hiếu</p>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Occupancy Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Công suất phòng (Occupancy)</h3>
                    <span class="text-blue-500 bg-blue-50 p-2 rounded-lg">📊</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold text-gray-800">78%</span>
                    <span class="text-green-500 text-sm font-medium">+2.5%</span>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Doanh thu (Revenue)</h3>
                    <span class="text-emerald-500 bg-emerald-50 p-2 rounded-lg">💰</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold text-gray-800">4.2 tỷ</span>
                    <span class="text-gray-400 text-sm">VND</span>
                </div>
            </div>

        </div>
    </div>

</body>
</html>