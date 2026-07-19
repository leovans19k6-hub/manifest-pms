<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        [
            'code' => 'housekeeping.tasks.view',
            'name' => 'View Housekeeping Tasks',
            'group' => 'housekeeping',
            'description' => 'View housekeeping tasks.',
        ],
        [
            'code' => 'housekeeping.tasks.create',
            'name' => 'Create Housekeeping Tasks',
            'group' => 'housekeeping',
            'description' => 'Create housekeeping tasks.',
        ],
        [
            'code' => 'housekeeping.tasks.update',
            'name' => 'Update Housekeeping Tasks',
            'group' => 'housekeeping',
            'description' => 'Update housekeeping tasks.',
        ],
        [
            'code' => 'housekeeping.tasks.assign',
            'name' => 'Assign Housekeeping Tasks',
            'group' => 'housekeeping',
            'description' => 'Assign housekeeping tasks.',
        ],
        [
            'code' => 'housekeeping.tasks.complete',
            'name' => 'Complete Housekeeping Tasks',
            'group' => 'housekeeping',
            'description' => 'Complete housekeeping tasks.',
        ],
        [
            'code' => 'housekeeping.tasks.delete',
            'name' => 'Delete Housekeeping Tasks',
            'group' => 'housekeeping',
            'description' => 'Delete housekeeping tasks.',
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