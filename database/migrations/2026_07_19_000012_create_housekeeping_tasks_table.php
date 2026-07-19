<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUlid('property_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUlid('unit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUlid('reservation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignUlid('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status', 30);

            $table->string('type', 30);

            $table->unsignedTinyInteger('priority')
                ->default(3);

            $table->timestamp('scheduled_at')->nullable();

            $table->timestamp('started_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'organization_id',
                'status',
            ]);

            $table->index([
                'property_id',
                'status',
            ]);

            $table->index([
                'unit_id',
                'status',
            ]);

            $table->index([
                'assigned_to',
                'status',
            ]);

            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
};