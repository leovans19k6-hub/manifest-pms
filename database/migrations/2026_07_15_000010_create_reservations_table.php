<?php

use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignUlid('organization_id');

            $table->foreignUlid('property_id');

            $table->foreignUlid('unit_id');

            $table->string('code', 50);
            $table->unique(
                ['organization_id', 'code'],
                'reservations_org_code_unique',
            );

            $table->string('status', 30)
                ->default(
                    ReservationStatus::Reserved->value,
                );
            $table->string('source', 30)
                ->default(
                    ReservationSource::Website->value,
                );

            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->string('guest_email')->nullable();

            $table->unsignedInteger('adults')->default(1);

            $table->unsignedInteger('children')->default(0);

            $table->dateTime('check_in');
            $table->dateTime('check_out');

            $table->text('notes')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->foreign(
                ['organization_id', 'property_id'],
                'reservations_organization_property_foreign',
            )
                ->references(['organization_id', 'id'])
                ->on('properties')
                ->cascadeOnDelete();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();

            $table->index(
                ['organization_id', 'property_id', 'status'],
                'reservations_org_property_status_index',
            );
            $table->index(
                ['organization_id', 'unit_id'],
                'reservations_org_unit_index',
            );
            $table->index(
                ['organization_id', 'check_in', 'check_out'],
                'reservations_org_stay_index',
            );

            $table->index([
                'status',
                'check_in',
                'check_out',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
