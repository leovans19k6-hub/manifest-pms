<?php

namespace Database\Seeders;

use Domain\Foundation\Models\Organization;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Property\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->each(function (Organization $organization): void {
            $name = $organization->name.' Demo Property';

            Property::query()->updateOrCreate(
                ['organization_id' => $organization->id, 'code' => 'DEMO-001'],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'type' => PropertyType::Villa,
                    'status' => PropertyStatus::Active,
                    'timezone' => $organization->timezone,
                    'currency' => $organization->currency,
                ],
            );
        });
    }
}
