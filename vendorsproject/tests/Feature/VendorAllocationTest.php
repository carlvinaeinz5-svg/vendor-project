<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_stall_and_vendor_can_see_stall_and_goods_category(): void
    {
        [$category, $vendorUser, $vendor] = $this->createVendor();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.vendor.status', $vendor), [
                'status' => 'approved',
                'stall_location' => 'Lane D, Stall 24',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee($category->name)
            ->assertSee('Lane D, Stall 24');
    }

    public function test_admin_cannot_assign_a_stall_location_already_in_use(): void
    {
        [, , $existingVendor] = $this->createVendor(['stall_location' => 'Lane A, Stall 12']);
        [, , $vendorToAllocate] = $this->createVendor();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.vendor.status', $vendorToAllocate), [
                'status' => 'approved',
                'stall_location' => $existingVendor->stall_location,
            ])
            ->assertSessionHasErrors('stall_location');

        $this->assertDatabaseHas('vendors', [
            'id' => $vendorToAllocate->id,
            'stall_location' => null,
        ]);
    }

    private function createVendor(array $attributes = []): array
    {
        $category = Category::firstOrCreate(
            ['slug' => 'fresh-produce'],
            ['name' => 'Fresh produce'],
        );
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create(array_merge([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'store_name' => 'Market Stall',
            'phone' => '0700000000',
            'status' => 'pending',
        ], $attributes));

        return [$category, $user, $vendor];
    }
}
