<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_page_offers_customer_and_vendor_account_types(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee('name="role" value="customer" checked', false)
            ->assertSee('name="role" value="vendor"', false);

        $this->get(route('vendor.register'))
            ->assertOk()
            ->assertSee('name="role" value="vendor" checked', false);
    }

    public function test_customer_can_create_an_account_without_vendor_details(): void
    {
        $response = $this->post(route('register.post'), [
            'role' => 'customer',
            'full_name' => 'Market Customer',
            'email' => 'customer@example.com',
            'password' => 'customer-password',
            'password_confirmation' => 'customer-password',
        ]);

        $user = User::where('email', 'customer@example.com')->firstOrFail();

        $response->assertRedirect(route('marketplace'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('customer', $user->role);
        $this->assertNull($user->phone);
        $this->assertDatabaseCount('vendors', 0);
    }

    public function test_vendor_can_apply_and_is_created_as_pending(): void
    {
        $category = Category::create(['name' => 'Produce', 'slug' => 'produce']);

        $response = $this->post(route('register.post'), [
            'role' => 'vendor',
            'full_name' => 'Market Vendor',
            'email' => 'vendor@example.com',
            'phone' => '0700000000',
            'store_name' => 'Fresh Stall',
            'category_id' => $category->id,
            'password' => 'vendor-password',
            'password_confirmation' => 'vendor-password',
        ]);

        $user = User::where('email', 'vendor@example.com')->firstOrFail();
        $vendor = Vendor::where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect(route('vendor.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('vendor', $user->role);
        $this->assertSame('pending', $vendor->status);
        $this->assertSame('Fresh Stall', $vendor->store_name);
    }

    public function test_registration_rejects_admin_and_other_unlisted_roles(): void
    {
        $this->post(route('register.post'), [
            'role' => 'admin',
            'full_name' => 'Unexpected Admin',
            'email' => 'admin@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_marketplace_seeded_payment_methods_match_their_orders(): void
    {
        $this->seed();

        $order = Order::with('payments')->where('number', 'ORD-2026-0147')->firstOrFail();

        foreach ($order->payments as $payment) {
            $this->assertSame($order->payment_method, $payment->method);
        }
    }
}
