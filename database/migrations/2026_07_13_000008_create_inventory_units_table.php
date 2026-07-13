<?php

use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->unique(
                ['organization_id', 'id'],
                'properties_organization_id_id_unique',
            );
        });

        Schema::create('units', function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignUlid('organization_id');
            $table->foreignUlid('property_id');

            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('slug', 180);

            $table->string('type', 30)
                ->default(UnitType::Room->value);

            $table->string('status', 30)
                ->default(UnitStatus::Draft->value);

            $table->unsignedInteger('capacity_adults')->default(2);
            $table->unsignedInteger('capacity_children')->default(0);
            $table->unsignedInteger('bedrooms')->default(1);
            $table->unsignedInteger('bathrooms')->default(1);
            $table->unsignedInteger('base_occupancy')->default(1);
            $table->unsignedInteger('max_occupancy')->default(2);
            $table->unsignedInteger('sort_order')->default(0);

            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->foreign(
                ['organization_id', 'property_id'],
                'units_organization_property_foreign',
            )
                ->references(['organization_id', 'id'])
                ->on('properties')
                ->cascadeOnDelete();

            $table->unique(
                ['organization_id', 'property_id', 'code'],
                'units_org_property_code_unique',
            );

            $table->unique(
                ['organization_id', 'property_id', 'slug'],
                'units_org_property_slug_unique',
            );

            $table->index(
                ['organization_id', 'property_id', 'status'],
                'units_org_property_status_index',
            );

            $table->index(
                ['organization_id', 'property_id', 'type'],
                'units_org_property_type_index',
            );

            $table->index(
                ['organization_id', 'property_id', 'sort_order'],
                'units_org_property_sort_index',
            );
        });

        $this->addDatabaseInvariants();
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement(
                'DROP TRIGGER IF EXISTS units_invariants_insert_check',
            );

            DB::statement(
                'DROP TRIGGER IF EXISTS units_invariants_update_check',
            );
        }

        Schema::dropIfExists('units');

        Schema::table('properties', function (Blueprint $table): void {
            $table->dropUnique(
                'properties_organization_id_id_unique',
            );
        });
    }

    private function addDatabaseInvariants(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->addSqliteDatabaseInvariants();

            return;
        }

        $this->addMariaDbDatabaseInvariants();
    }

    private function addMariaDbDatabaseInvariants(): void
    {
        DB::statement(
            'ALTER TABLE units '
            .'ADD CONSTRAINT units_base_occupancy_check '
            .'CHECK (base_occupancy <= max_occupancy)',
        );

        DB::statement(
            'ALTER TABLE units '
            .'ADD CONSTRAINT units_numeric_non_negative_check '
            .'CHECK ('
            .'capacity_adults >= 0 '
            .'AND capacity_children >= 0 '
            .'AND bedrooms >= 0 '
            .'AND bathrooms >= 0 '
            .'AND base_occupancy >= 0 '
            .'AND max_occupancy >= 0 '
            .'AND sort_order >= 0'
            .')',
        );

        DB::statement(
            'ALTER TABLE units '
            .'ADD CONSTRAINT units_type_check '
            .'CHECK (type IN ('
            ."'room', "
            ."'villa', "
            ."'house', "
            ."'apartment', "
            ."'bed', "
            ."'other'"
            .'))',
        );

        DB::statement(
            'ALTER TABLE units '
            .'ADD CONSTRAINT units_status_check '
            .'CHECK (status IN ('
            ."'draft', "
            ."'active', "
            ."'inactive', "
            ."'maintenance', "
            ."'archived'"
            .'))',
        );
    }

    private function addSqliteDatabaseInvariants(): void
    {
        DB::statement(
            <<<'SQL'
CREATE TRIGGER units_invariants_insert_check
BEFORE INSERT ON units
FOR EACH ROW
WHEN
    NEW.base_occupancy > NEW.max_occupancy
    OR NEW.capacity_adults < 0
    OR NEW.capacity_children < 0
    OR NEW.bedrooms < 0
    OR NEW.bathrooms < 0
    OR NEW.base_occupancy < 0
    OR NEW.max_occupancy < 0
    OR NEW.sort_order < 0
    OR NEW.type NOT IN (
        'room',
        'villa',
        'house',
        'apartment',
        'bed',
        'other'
    )
    OR NEW.status NOT IN (
        'draft',
        'active',
        'inactive',
        'maintenance',
        'archived'
    )
BEGIN
    SELECT RAISE(ABORT, 'units_database_invariant_failed');
END
SQL,
        );

        DB::statement(
            <<<'SQL'
CREATE TRIGGER units_invariants_update_check
BEFORE UPDATE ON units
FOR EACH ROW
WHEN
    NEW.base_occupancy > NEW.max_occupancy
    OR NEW.capacity_adults < 0
    OR NEW.capacity_children < 0
    OR NEW.bedrooms < 0
    OR NEW.bathrooms < 0
    OR NEW.base_occupancy < 0
    OR NEW.max_occupancy < 0
    OR NEW.sort_order < 0
    OR NEW.type NOT IN (
        'room',
        'villa',
        'house',
        'apartment',
        'bed',
        'other'
    )
    OR NEW.status NOT IN (
        'draft',
        'active',
        'inactive',
        'maintenance',
        'archived'
    )
BEGIN
    SELECT RAISE(ABORT, 'units_database_invariant_failed');
END
SQL,
        );
    }
};
