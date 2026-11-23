<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Notifications\OrderActivityNotification;
use Tests\TestCase;

class VendorProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_add_and_edit_a_product_with_an_uploaded_photo(): void
    {
        Storage::fake('public');
        [$user, $vendor, $category] = $this->createVendor();
        $photo = UploadedFile::fake()->createWithContent(
            'tomatoes.jpg',
            file_get_contents(public_path('assets/images/tomatoes.jpg')),
        );

        $this->actingAs($user)
            ->post(route('vendor.products.store'), [
                'name' => 'Garden tomatoes',
                'unit' => '1 kg',
                'description' => 'Fresh tomatoes',
                'price' => 5000,
                'stock' => 10,
                'image' => $photo,
            ])
            ->assertSessionHasNoErrors();

        $product = Product::where('name', 'Garden tomatoes')->firstOrFail();
        $this->assertSame($category->id, $product->category_id);
        Storage::disk('public')->assertExists($product->image);

        $this->put(route('vendor.products.update', $product), [
            'name' => 'Ripe tomatoes',
            'unit' => '1 kg',
            'description' => 'Fresh ripe tomatoes',
            'price' => 6000,
            'stock' => 8,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Ripe tomatoes',
            'price' => 6000,
            'stock' => 8,
            'category_id' => $category->id,
        ]);
    }

    public function test_vendor_cannot_edit_another_vendors_product(): void
    {
        [$firstUser] = $this->createVendor('First stall');
        [, $secondVendor, $category] = $this->createVendor('Second stall');
        $product = $secondVendor->products()->create([
            'category_id' => $category->id,
            'name' => 'Other vendor product',
            'unit' => 'item',
            'price' => 1000,
            'stock' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($firstUser)
            ->put(route('vendor.products.update', $product), [
                'name' => 'Changed product',
                'unit' => 'item',
                'price' => 1000,
                'stock' => 1,
            ])
            ->assertForbidden();
    }

    public function test_vendor_can_confirm_cash_received_and_notify_the_customer(): void
    {
        Notification::fake();
        [$vendorUser, $vendor] = $this->createVendor('Cash stall');
        $customer = User::factory()->create();
        $order = Order::create([
            'number' => 'ORD-CASH-0001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'phone' => '0700000000',
            'payment_method' => 'Cash on delivery / collection',
            'total' => 1000,
            'status' => 'pending',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'method' => 'Cash on delivery / collection',
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $this->actingAs($vendorUser)
            ->patch(route('vendor.payment.confirm', $payment))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'confirmed',
        ]);
        Notification::assertSentTo($customer, OrderActivityNotification::class);
    }

    public function test_vendor_can_update_delivery_handoff_status_and_customer_sees_the_address(): void
    {
        Notification::fake();
        [$vendorUser, $vendor, $category] = $this->createVendor('Delivery stall');
        $customer = User::factory()->create();
        $product = $vendor->products()->create([
            'category_id' => $category->id,
            'name' => 'Fresh greens',
            'unit' => 'bunch',
            'price' => 2000,
            'stock' => 4,
            'is_active' => true,
        ]);
        $order = Order::create([
            'number' => 'ORD-DELIVERY-0001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'phone' => '0700000000',
            'fulfillment_method' => 'delivery',
            'delivery_address' => 'Nakawa Trading Centre, Kampala',
            'payment_method' => 'Cash on delivery / collection',
            'total' => 2000,
            'status' => 'pending',
        ]);
        $item = $order->items()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 2000,
            'subtotal' => 2000,
            'status' => 'pending',
        ]);

        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Nakawa Trading Centre, Kampala')
            ->assertSee('Ready for delivery');

        $this->patch(route('vendor.order.status', $item), ['status' => 'ready'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'status' => 'ready']);
        Notification::assertSentTo($customer, OrderActivityNotification::class);
    }

    private function createVendor(string $storeName = 'Market stall'): array
    {
        $category = Category::create([
            'name' => $storeName.' goods',
            'slug' => str_replace(' ', '-', strtolower($storeName)).'-goods',
        ]);
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'store_name' => $storeName,
            'phone' => '0700000000',
            'status' => 'approved',
        ]);

        return [$user, $vendor, $category];
    }
}
