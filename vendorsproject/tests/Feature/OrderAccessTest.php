<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\OrderActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_and_order_history_require_sign_in(): void
    {
        $this->get(route('checkout'))->assertRedirect(route('login'));
        $this->post(route('checkout.place'))->assertRedirect(route('login'));
        $this->get(route('orders'))->assertRedirect(route('login'));
    }

    public function test_login_returns_customers_to_the_checkout_they_requested(): void
    {
        $customer = User::factory()->create(['password' => bcrypt('password')]);

        $this->get(route('checkout'))->assertRedirect(route('login'));
        $this->post(route('login.post'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('checkout'));
    }

    public function test_customer_only_sees_their_own_orders_and_cannot_open_another_confirmation(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $ownOrder = $this->createOrder($customer, 'ORD-OWN-0001');
        $otherOrder = $this->createOrder($otherCustomer, 'ORD-OTHER-0001');

        $this->actingAs($customer)
            ->get(route('orders'))
            ->assertOk()
            ->assertSee($ownOrder->number)
            ->assertDontSee($otherOrder->number);

        $this->get(route('orders.confirmation', $otherOrder))->assertForbidden();
    }

    public function test_checkout_uses_current_database_prices_and_sends_cash_order_updates(): void
    {
        Notification::fake();
        [$vendorUser, $product] = $this->createProduct();
        $customer = User::factory()->create();
        $this->assertTrue($customer->email_notifications);
        $this->assertTrue($vendorUser->email_notifications);

        $response = $this->actingAs($customer)
            ->withSession(['cart' => [
                $product->id => [
                    'product' => ['id' => $product->id, 'name' => $product->name, 'price' => 1, 'vendor' => 'old vendor value'],
                    'quantity' => 2,
                ],
            ]])
            ->post(route('checkout.place'), [
                'full_name' => $customer->name,
                'phone' => '0700000000',
                'fulfillment_method' => 'delivery',
                'delivery_address' => 'Nakawa Trading Centre, Kampala',
                'payment_method' => 'Cash on delivery / collection',
            ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.confirmation', $order));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'fulfillment_method' => 'delivery',
            'delivery_address' => 'Nakawa Trading Centre, Kampala',
        ]);
        $this->actingAs($customer)
            ->get(route('orders.confirmation', $order))
            ->assertOk()
            ->assertSee('Nakawa Trading Centre, Kampala')
            ->assertSee('Delivery');
        $this->assertSame(10000, $order->total);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'Cash on delivery / collection',
            'amount' => 10000,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
        $this->assertSame([], session('cart', []));

        Notification::assertSentTo($customer, OrderActivityNotification::class);
        Notification::assertSentTo($vendorUser, OrderActivityNotification::class);
    }

    public function test_delivery_address_is_required_and_collection_does_not_store_one(): void
    {
        [, $product] = $this->createProduct();
        $customer = User::factory()->create();
        $cart = [
            $product->id => [
                'product' => ['id' => $product->id, 'name' => $product->name, 'price' => 5000],
                'quantity' => 1,
            ],
        ];

        $this->actingAs($customer)
            ->withSession(['cart' => $cart])
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('Lane A, Stall 4');

        $this->actingAs($customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.place'), [
                'full_name' => $customer->name,
                'phone' => '0700000000',
                'fulfillment_method' => 'delivery',
                'payment_method' => 'Cash on delivery / collection',
            ])
            ->assertSessionHasErrors('delivery_address');

        $this->assertDatabaseCount('orders', 0);

        $this->withSession(['cart' => $cart])
            ->post(route('checkout.place'), [
                'full_name' => $customer->name,
                'phone' => '0700000000',
                'fulfillment_method' => 'collection',
                'payment_method' => 'Cash on delivery / collection',
            ])
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('collection', $order->fulfillment_method);
        $this->assertNull($order->delivery_address);
    }

    public function test_customer_can_select_mobile_money_and_vendor_can_confirm_receipt(): void
    {
        Notification::fake();
        [$vendorUser, $product] = $this->createProduct();
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->withSession(['cart' => [
                $product->id => ['product' => ['id' => $product->id], 'quantity' => 1],
            ]])
            ->post(route('checkout.place'), [
                'full_name' => $customer->name,
                'phone' => '0700000000',
                'fulfillment_method' => 'collection',
                'payment_method' => 'Mobile Money (pay vendor directly)',
            ])
            ->assertRedirect();

        $payment = Payment::firstOrFail();
        $this->assertSame('Mobile Money (pay vendor directly)', $payment->method);
        $this->assertSame('Mobile Money (pay vendor directly)', $payment->order->payment_method);

        $this->actingAs($vendorUser)
            ->patch(route('vendor.payment.confirm', $payment))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'confirmed',
        ]);
    }

    private function createOrder(User $customer, string $number): Order
    {
        return Order::create([
            'number' => $number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'phone' => '0700000000',
            'payment_method' => 'Cash on delivery / collection',
            'total' => 1000,
            'status' => 'pending',
        ]);
    }

    private function createProduct(): array
    {
        $category = Category::create(['name' => 'Produce', 'slug' => 'produce']);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'category_id' => $category->id,
            'store_name' => 'Green stall',
            'stall_location' => 'Lane A, Stall 4',
            'phone' => '0700000001',
            'status' => 'approved',
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Spinach bunch',
            'unit' => 'bunch',
            'price' => 5000,
            'stock' => 5,
            'is_active' => true,
        ]);

        return [$vendorUser, $product];
    }
}
