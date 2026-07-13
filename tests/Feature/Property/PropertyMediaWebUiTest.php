<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyAssetFactory;
use Database\Factories\PropertyDocumentFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyMediaWebUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $property = PropertyFactory::new()->create();

        $this->get(
            "/admin/properties/{$property->id}/media",
        )->assertRedirect('/login');
    }

    public function test_user_with_asset_view_permission_can_view_tenant_assets(): void
    {
        [$user, $org, $property] = $this->principal([
            'property.media.view',
        ]);

        PropertyAssetFactory::new()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'original_name' => 'visible.jpg',
        ]);

        $foreignProperty = PropertyFactory::new()->create();

        PropertyAssetFactory::new()->create([
            'organization_id' => $foreignProperty->organization_id,
            'property_id' => $foreignProperty->id,
            'original_name' => 'foreign.jpg',
        ]);

        $this->actingAs($user)
            ->get("/admin/properties/{$property->id}/media")
            ->assertOk()
            ->assertSee('Hình ảnh & Media', false)
            ->assertSee('1 media')
            ->assertDontSee('Tài liệu');
    }

    public function test_user_with_document_view_permission_can_view_tenant_documents(): void
    {
        [$user, $org, $property] = $this->principal([
            'property.documents.view',
        ]);

        PropertyDocumentFactory::new()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'original_name' => 'visible.pdf',
        ]);

        $this->actingAs($user)
            ->get("/admin/properties/{$property->id}/media")
            ->assertOk()
            ->assertSee('Tài liệu')
            ->assertSee('1 tài liệu')
            ->assertDontSee('Hình ảnh & Media');
    }

    public function test_user_without_any_media_view_permission_is_forbidden(): void
    {
        [$user, , $property] = $this->principal([]);

        $this->actingAs($user)
            ->get("/admin/properties/{$property->id}/media")
            ->assertForbidden();
    }

    public function test_foreign_property_is_not_found(): void
    {
        [$user] = $this->principal([
            'property.media.view',
            'property.documents.view',
        ]);

        $foreignProperty = PropertyFactory::new()->create();

        $this->actingAs($user)
            ->get("/admin/properties/{$foreignProperty->id}/media")
            ->assertNotFound();
    }

    public function test_media_filters_are_validated(): void
    {
        [$user, , $property] = $this->principal([
            'property.media.view',
        ]);

        $this->actingAs($user)
            ->get(
                "/admin/properties/{$property->id}/media".
                '?asset_kind=invalid'.
                '&asset_per_page=101',
            )
            ->assertSessionHasErrors([
                'asset_kind',
                'asset_per_page',
            ]);
    }

    private function principal(array $permissions): array
    {
        $org = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();

        $membership = OrganizationUser::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'status' => 'active',
            'is_default' => true,
        ]);

        if ($permissions !== []) {
            $role = RoleFactory::new()->create([
                'organization_id' => $org->id,
            ]);

            foreach ($permissions as $code) {
                $permission = PermissionFactory::new()->create([
                    'code' => $code,
                ]);

                $role->permissions()->attach($permission);
            }

            $membership->roles()->attach($role);
        }

        $property = PropertyFactory::new()->create([
            'organization_id' => $org->id,
        ]);

        return [$user, $org, $property, $membership];
    }
}
