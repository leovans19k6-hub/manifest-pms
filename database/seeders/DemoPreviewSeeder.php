<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class DemoPreviewSeeder extends Seeder
{
    /**
     * Chạy seed dữ liệu cho môi trường Preview ALPHA 1.
     */
    public function run(): void
    {
        // 1. Tạo Organizations
        $manifestGlobalId = DB::table('organizations')->insertGetId([
            'name' => 'Manifest Global',
            'slug' => 'manifest-global',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $villaHaiPhongId = DB::table('organizations')->insertGetId([
            'name' => 'Villa Hải Phòng',
            'slug' => 'villa-hai-phong',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Tạo Admin User
        $admin = User::create([
            'name' => 'Trung Hiếu',
            'email' => 'hieufhhp@gmail.com',
            'password' => Hash::make('password123'), // Mật khẩu demo
            'status' => 'active',
            'created_at' => Carbon::now(),
        ]);

        // 3. Gán User vào Organizations (Organization Membership)
        DB::table('organization_memberships')->insert([
            [
                'user_id' => $admin->id,
                'organization_id' => $manifestGlobalId,
                'role' => 'admin',
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => $admin->id,
                'organization_id' => $villaHaiPhongId,
                'role' => 'manager',
                'created_at' => Carbon::now(),
            ]
        ]);
    }
}