<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Vendor;
use App\Notifications\OrderActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class VendorController extends Controller
{
    public function dashboard()
    {
        $vendor = $this->currentVendor();
        $items = $vendor->orderItems()->with(['order', 'product'])->latest()->get();
        $products = $vendor->products()->latest()->get();
        $payments = Payment::with('order')->where('vendor_id', $vendor->id)->latest()->get();

        return view('dashboards.vendor', compact('vendor', 'items', 'products', 'payments'));
    }

    public function storeProduct(Request $request)
    {
        $vendor = $this->currentVendor();
        $data = $this->validateProduct($request);
        $image = $request->hasFile('image') ? $this->storeImage($request) : null;
        unset($data['image']);

        $vendor->products()->create([
            ...$data,
            'category_id' => $vendor->category_id,
            'image' => $image,
            'is_active' => true,
        ]);

        return back()->with('success', 'Product added to your store.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $vendor = $this->currentVendor();
        $this->authorizeProduct($vendor, $product);
        $data = $this->validateProduct($request);
        unset($data['image']);

        if ($request->hasFile('image')) {
            $oldImage = $product->image;
            $data['image'] = $this->storeImage($request);
            $product->update($data);
            $this->removeStoredImage($oldImage);
        } else {
            $product->update($data);
        }

        return back()->with('success', 'Product details updated.');
    }

    public function destroyProduct(Product $product)
    {
        $vendor = $this->currentVendor();
        $this->authorizeProduct($vendor, $product);

        if (OrderItem::where('product_id', $product->id)->exists()) {
            $product->update(['is_active' => false]);

            return back()->with('success', 'This product has order history, so it was hidden from the marketplace instead of deleted.');
        }

        $image = $product->image;
        $product->delete();
        $this->removeStoredImage($image);

        return back()->with('success', 'Product deleted.');
    }

    public function updateItemStatus(Request $request, OrderItem $item)
    {
        $vendor = $this->currentVendor();
        abort_unless((int) $item->vendor_id === (int) $vendor->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,ready,completed,cancelled,unavailable'],
        ]);

        $oldStatus = $item->status;
        DB::transaction(function () use ($item, $data) {
            $lockedItem = OrderItem::lockForUpdate()->findOrFail($item->id);
            $wasUnavailable = in_array($lockedItem->status, ['cancelled', 'unavailable'], true);
            $willBeUnavailable = in_array($data['status'], ['cancelled', 'unavailable'], true);

            if ($wasUnavailable !== $willBeUnavailable) {
                $product = Product::lockForUpdate()->findOrFail($lockedItem->product_id);
                if ($willBeUnavailable) {
                    $product->increment('stock', $lockedItem->quantity);
                } else {
                    if ($product->stock < $lockedItem->quantity) {
                        throw ValidationException::withMessages([
                            'status' => 'There is not enough stock to reopen this order item.',
                        ]);
                    }
                    $product->decrement('stock', $lockedItem->quantity);
                }
            }

            $lockedItem->update(['status' => $data['status']]);

            $statuses = OrderItem::where('order_id', $lockedItem->order_id)->pluck('status');
            $orderStatus = match (true) {
                $statuses->every(fn ($status) => $status === 'completed') => 'completed',
                $statuses->every(fn ($status) => in_array($status, ['cancelled', 'unavailable'], true)) => 'cancelled',
                $statuses->contains(fn ($status) => $status !== 'pending') => 'in_progress',
                default => 'pending',
            };
            $lockedItem->order()->update(['status' => $orderStatus]);
        });

        if ($oldStatus !== $data['status']) {
            $order = $item->order()->with('customer')->firstOrFail();
            if ($order->customer && $order->customer->email_notifications) {
                $fulfillment = $order->handoffLabel();
                $statusMessage = match ($data['status']) {
                    'confirmed' => 'confirmed your order',
                    'ready' => 'marked '.$item->product->name.' ready for '.$fulfillment,
                    'completed' => $order->fulfillment_method === 'delivery'
                        ? 'marked '.$item->product->name.' as delivered'
                        : 'marked '.$item->product->name.' as collected',
                    'cancelled' => 'cancelled '.$item->product->name,
                    'unavailable' => 'marked '.$item->product->name.' unavailable',
                    default => 'returned '.$item->product->name.' to pending',
                };
                $order->customer->notify(new OrderActivityNotification(
                    'A vendor updated your Nakawa Market order',
                    $item->vendor->store_name.' '.$statusMessage.' for order '.$order->number.'.',
                    'View order',
                    'orders.confirmation',
                    ['order' => $order->id],
                ));
            }
        }

        return back()->with('success', 'Order status updated.');
    }

    public function confirmPayment(Payment $payment)
    {
        $vendor = $this->currentVendor();
        abort_unless((int) $payment->vendor_id === (int) $vendor->id, 403);

        if ($payment->status !== 'confirmed') {
            $payment->update(['status' => 'confirmed', 'confirmed_at' => now()]);
            $order = $payment->order()->with('customer')->firstOrFail();
            if ($order->customer && $order->customer->email_notifications) {
                $order->customer->notify(new OrderActivityNotification(
                    'Payment received',
                    $vendor->store_name.' confirmed UGX '.number_format($payment->amount).' via '.$payment->method.' for order '.$order->number.'.',
                    'View order',
                    'orders.confirmation',
                    ['order' => $order->id],
                ));
            }
        }

        return back()->with('success', 'Payment marked as received.');
    }

    private function currentVendor(): Vendor
    {
        abort_unless(Auth::check() && Auth::user()->role === 'vendor', 403);

        $vendor = Auth::user()->vendor;
        abort_unless($vendor, 403);

        return $vendor;
    }

    private function authorizeProduct(Vendor $vendor, Product $product): void
    {
        abort_unless((int) $product->vendor_id === (int) $vendor->id, 403);
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);
    }

    private function storeImage(Request $request): string
    {
        $path = $request->file('image')->store('products', 'public');
        if (!$path) {
            throw new RuntimeException('Unable to store the uploaded product image.');
        }

        return $path;
    }

    private function removeStoredImage(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'products/')) {
            return;
        }

        if (!Storage::disk('public')->delete($path)) {
            Log::warning('Unable to delete stored product image.', ['path' => $path]);
        }
    }
}
