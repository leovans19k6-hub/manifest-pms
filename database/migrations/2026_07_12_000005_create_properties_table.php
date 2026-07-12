<?php

use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('slug', 180);
            $table->string('type', 30)->default(PropertyType::Villa->value);
            $table->string('status', 30)->default(PropertyStatus::Draft->value);
            $table->string('timezone', 64)->default('UTC');
            $table->string('currency', 3)->default('VND');
            $table->text('address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
