<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        [
            'code' => 'reservation.reservations.view',
            'name' => 'View Reservations',
            'group' => 'reservation',
            'description' => 'View reservations.',
        ],
        [
            'code' => 'reservation.reservations.create',
            'name' => 'Create Reservations',
            'group' => 'reservation',
            'description' => 'Create reservations.',
        ],
        [
            'code' => 'reservation.reservations.update',
            'name' => 'Update Reservations',
            'group' => 'reservation',
            'description' => 'Update reservations.',
        ],
        [
            'code' => 'reservation.reservations.cancel',
            'name' => 'Cancel Reservations',
            'group' => 'reservation',
            'description' => 'Cancel reservations.',
        ],
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'id' => (string) str()->ulid(),
                ...$permission,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('permissions')
                ->where('code', $permission['code'])
                ->update([
                    'name' => $permission['name'],
                    'group' => $permission['group'],
                    'description' => $permission['description'],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn(
                'code',
                array_column(self::PERMISSIONS, 'code'),
            )
            ->delete();
    }
};
