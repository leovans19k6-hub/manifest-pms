<?php

namespace Database\Factories;

use Domain\Property\Models\Property;
use Domain\Property\Models\PropertyAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyAssetFactory extends Factory
{
    protected $model = PropertyAsset::class;

    public function definition(): array
    {
        $p = Property::factory()->create();

        return ['organization_id' => $p->organization_id, 'property_id' => $p->id, 'kind' => 'image', 'disk' => 'local', 'storage_key' => 'test/'.fake()->uuid(), 'original_name' => 'photo.jpg', 'mime_type' => 'image/jpeg', 'size_bytes' => 100, 'checksum' => str_repeat('a', 64), 'metadata' => []];
    }
}
