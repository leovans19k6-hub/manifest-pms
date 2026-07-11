<?php

namespace Tests\Unit\Architecture;

use App\Providers\DomainServiceProvider;
use Domain\Shared\Contracts\DomainService;
use Domain\Shared\DTO\DataTransferObject;
use Domain\Shared\Exceptions\DomainException;
use Domain\Shared\Traits\HasUlids;
use Tests\TestCase;

class ArchitectureFoundationTest extends TestCase
{
    public function test_domain_service_provider_is_registered(): void
    {
        $providers = require base_path('bootstrap/providers.php');

        $this->assertContains(
            DomainServiceProvider::class,
            $providers,
        );

        $this->assertArrayHasKey(
            DomainServiceProvider::class,
            app()->getLoadedProviders(),
        );

        $this->assertTrue(
            app()->getLoadedProviders()[
                DomainServiceProvider::class
            ],
        );
    }

    public function test_shared_kernel_foundation_classes_are_autoloadable(): void
    {
        $this->assertTrue(interface_exists(DomainService::class));
        $this->assertTrue(class_exists(DataTransferObject::class));
        $this->assertTrue(class_exists(DomainException::class));
        $this->assertTrue(trait_exists(HasUlids::class));
    }

    public function test_business_modules_are_not_prematurely_implemented(): void
    {
        foreach (['Foundation', 'Property', 'Inventory', 'Reservation', 'Guest', 'Finance', 'Operation', 'Reporting', 'System'] as $module) {
            $this->assertDirectoryDoesNotExist(base_path("app/Domain/{$module}"));
        }
    }
}
