<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_assets', function (Blueprint $t) {
            $t->ulid('id')->primary();
            $t->ulid('organization_id');
            $t->ulid('property_id');
            $t->string('kind', 30);
            $t->string('disk', 50);
            $t->string('storage_key', 500);
            $t->string('original_name', 255);
            $t->string('mime_type', 150);
            $t->unsignedBigInteger('size_bytes');
            $t->string('checksum', 64)->nullable();
            $t->json('metadata')->nullable();
            $t->timestamps();
            $t->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $t->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
            $t->unique(['disk', 'storage_key']);
            $t->index(['organization_id', 'property_id', 'kind']);
        });
        Schema::create('property_documents', function (Blueprint $t) {
            $t->ulid('id')->primary();
            $t->ulid('organization_id');
            $t->ulid('property_id');
            $t->string('category', 50);
            $t->string('disk', 50);
            $t->string('storage_key', 500);
            $t->string('original_name', 255);
            $t->string('mime_type', 150);
            $t->unsignedBigInteger('size_bytes');
            $t->string('checksum', 64)->nullable();
            $t->json('metadata')->nullable();
            $t->timestamps();
            $t->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $t->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
            $t->unique(['disk', 'storage_key']);
            $t->index(['organization_id', 'property_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_documents');
        Schema::dropIfExists('property_assets');
    }
};
