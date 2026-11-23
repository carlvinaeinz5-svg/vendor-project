<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_an_unused_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Baked goods', 'slug' => 'baked-goods'])
            ->assertSessionHasNoErrors();

        $category = Category::where('slug', 'baked-goods')->firstOrFail();
        $this->patch(route('admin.categories.update', $category), [
            'name' => 'Bakery goods',
            'slug' => 'bakery-goods',
        ])->assertSessionHasNoErrors();

        $this->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_in_use_cannot_be_deleted_and_customers_cannot_manage_categories(): void
    {
        $category = Category::create(['name' => 'Fresh produce', 'slug' => 'fresh-produce']);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $vendorUser->id,
            'category_id' => $category->id,
            'store_name' => 'Greens Stall',
            'phone' => '0700000000',
            'status' => 'approved',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('admin.categories'))->assertForbidden();
    }
}
