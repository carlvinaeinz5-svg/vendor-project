<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Notifications\OrderActivityNotification;
use App\Services\CartContents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function checkout(Request $request, CartContents $cartContents)
    {
        $cart = $cartContents->refresh($request->session()->get('cart', []));
        $request->session()->put('cart', $cart);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        foreach ($cart as $line) {
            if (empty($line['product']['available']) || $line['quantity'] > $line['stock']) {
                return redirect()->route('cart')->with('error', 'A product in your cart is no longer available or has insufficient stock. Please review your cart.');
            }
        }

        $total = collect($cart)->sum(fn ($line) => $line['product']['price'] * $line['quantity']);
        $vendorParts = collect($cart)->groupBy(fn ($line) => $line['product']['vendor']);

        return view('checkout', [
            'cart' => $cart,
            'total' => $total,
            'vendorParts' => $vendorParts,
            'customer' => $request->user(),
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'fulfillment_method' => ['required', 'in:delivery,collection'],
            'delivery_address' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in(array_keys(Payment::METHODS))],
        ]);

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $order = DB::transaction(function () use ($cart, $data, $request) {
            $orderedProducts = [];
            $total = 0;

            foreach ($cart as $productId => $line) {
                $productId = (int) ($line['product']['id'] ?? $productId);
                $quantity = filter_var($line['quantity'] ?? null, FILTER_VALIDATE_INT);

                if ($productId < 1 || $quantity === false || $quantity < 1 || $quantity > 99) {
                    throw ValidationException::withMessages(['cart' => 'Your cart contains an invalid item. Please review it and try again.']);
                }

                $product = Product::with('vendor')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->find($productId);

                if (!$product || !$product->vendor || $product->vendor->status !== 'approved') {
                    throw ValidationException::withMessages(['cart' => 'A product in your cart is no longer available. Please review your cart.']);
                }

                if ($quantity > $product->stock) {
                    throw ValidationException::withMessages(['cart' => 'There is not enough stock for '.$product->name.'. Please update your cart.']);
                }

                $orderedProducts[] = ['product' => $product, 'quantity' => $quantity];
                $total += $product->price * $quantity;
            }

            $order = Order::create([
                'number' => 'ORD-'.now()->format('Y').'-'.Str::upper(Str::random(10)),
                'customer_id' => $request->user()->id,
                'customer_name' => $data['full_name'],
                'phone' => $data['phone'],
                'delivery_note' => $data['note'] ?? null,
                'fulfillment_method' => $data['fulfillment_method'],
                'delivery_address' => $data['fulfillment_method'] === 'delivery' ? $data['delivery_address'] : null,
                'payment_method' => $data['payment_method'],
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($orderedProducts as $line) {
                $product = $line['product'];
                $quantity = $line['quantity'];
                $subtotal = $product->price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'vendor_id' => $product->vendor_id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'status' => 'pending',
                ]);
                Payment::create([
                    'order_id' => $order->id,
                    'vendor_id' => $product->vendor_id,
                    'method' => $data['payment_method'],
                    'amount' => $subtotal,
                    'status' => 'pending',
                ]);
                Product::whereKey($product->id)->decrement('stock', $quantity);
            }

            return $order;
        });

        $request->session()->forget('cart');
        $order->load(['items.vendor.user', 'items.product']);

        $customer = $request->user();
        if ($customer->email_notifications) {
            $fulfillmentMessage = $order->fulfillment_method === 'delivery'
                ? ' Delivery is requested to: '.$order->delivery_address.'.'
                : ' You will collect from the vendor stalls.';
            $customer->notify(new OrderActivityNotification(
                'Your Nakawa Market order was placed',
                'Order '.$order->number.' has been sent to the vendors.'.$fulfillmentMessage.' Arrange '.$order->payment_method.' payment directly with each vendor; this site does not process payments.',
                'View order',
                'orders.confirmation',
                ['order' => $order->id],
            ));
        }

        foreach ($order->items->groupBy('vendor_id') as $vendorItems) {
            $vendor = $vendorItems->first()->vendor;
            $vendorUser = $vendor->user;
            if ($vendorUser && $vendorUser->email_notifications) {
                $fulfillmentMessage = $order->fulfillment_method === 'delivery'
                    ? 'Delivery address: '.$order->delivery_address.'.'
                    : 'Collection at your stall'.($vendor->stall_location ? ' ('.$vendor->stall_location.')' : '').'.';
                $fulfillmentMessage .= ' Customer contact: '.$order->customer_name.' ('.$order->phone.').';
                if ($order->delivery_note) {
                    $fulfillmentMessage .= ' Customer instructions: '.$order->delivery_note;
                }
                $vendorUser->notify(new OrderActivityNotification(
                    'A new Nakawa Market order needs your attention',
                    'Order '.$order->number.' includes '.implode(', ', $vendorItems->map(fn ($item) => $item->quantity.' × '.$item->product->name)->all()).'. '.$fulfillmentMessage,
                    'Open vendor dashboard',
                    'vendor.dashboard',
                ));
            }
        }

        return redirect()
            ->route('orders.confirmation', $order)
            ->with('success', 'Order '.$order->number.' has been placed. Arrange '.$order->payment_method.' payment directly with each vendor.');
    }

    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'items.vendor', 'payments'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return view('orders', compact('orders'));
    }

    public function confirmation(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        $order->load(['items.product', 'items.vendor', 'payments']);

        return view('orders.confirmation', compact('order'));
    }
}
