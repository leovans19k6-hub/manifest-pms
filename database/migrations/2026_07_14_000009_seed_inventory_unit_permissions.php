<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        [
            'code' => 'inventory.units.view',
            'name' => 'View Units',
            'group' => 'inventory',
            'description' => 'View inventory units.',
        ],
        [
            'code' => 'inventory.units.create',
            'name' => 'Create Units',
            'group' => 'inventory',
            'description' => 'Create inventory units.',
        ],
        [
            'code' => 'inventory.units.update',
            'name' => 'Update Units',
            'group' => 'inventory',
            'description' => 'Update inventory units.',
        ],
        [
            'code' => 'inventory.units.archive',
            'name' => 'Archive Units',
            'group' => 'inventory',
            'description' => 'Archive inventory units.',
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
