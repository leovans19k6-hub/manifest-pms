<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_assets', function (Blueprint $table): void {
            $table->unsignedInteger('position')
                ->default(0)
                ->after('kind');

            $table->index(
                [
                    'organization_id',
                    'property_id',
                    'position',
                ],
                'prop_assets_org_prop_position_idx',
            );
        });

        Schema::table('property_documents', function (Blueprint $table): void {
            $table->string('lifecycle_status', 30)
                ->default('active')
                ->after('category');

            $table->timestamp('archived_at')
                ->nullable()
                ->after('metadata');

            $table->index(
                [
                    'organization_id',
                    'property_id',
                    'lifecycle_status',
                ],
                'prop_docs_org_prop_lifecycle_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('property_assets', function (Blueprint $table): void {
            $table->dropIndex('prop_assets_org_prop_position_idx');
            $table->dropColumn('position');
        });

        Schema::table('property_documents', function (Blueprint $table): void {
            $table->dropIndex('prop_docs_org_prop_lifecycle_idx');

            $table->dropColumn([
                'lifecycle_status',
                'archived_at',
            ]);
        });
    }
};
