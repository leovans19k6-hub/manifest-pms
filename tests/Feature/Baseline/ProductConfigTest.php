<?php

namespace Tests\Feature\Baseline;

use Tests\TestCase;

class ProductConfigTest extends TestCase
{
    public function test_manifest_product_configuration_matches_verified_baseline(): void
    {
        $this->assertSame('Manifest Global', config('manifest.company'));
        $this->assertSame('Manifest Stay PMS', config('manifest.product'));
        $this->assertSame('0.1.0-dev', config('manifest.version'));
    }
}
