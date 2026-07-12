<?php

namespace Database\Factories;

use Domain\Property\Models\Property;
use Domain\Property\Models\PropertyDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyDocumentFactory extends Factory
{
    protected $model = PropertyDocument::class;

    public function definition(): array
    {
        $p = Property::factory()->create();

        return ['organization_id' => $p->organization_id, 'property_id' => $p->id, 'category' => 'legal', 'disk' => 'local', 'storage_key' => 'test/'.fake()->uuid(), 'original_name' => 'doc.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 100, 'checksum' => str_repeat('b', 64), 'metadata' => []];
    }
}
