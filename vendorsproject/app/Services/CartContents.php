<?php

namespace App\Services;

use App\Models\Product;

class CartContents
{
    public function refresh(array $cart): array
    {
        $productIds = collect($cart)
            ->map(fn ($line, $key) => (int) ($line['product']['id'] ?? $key))
            ->filter(fn ($id) => $id > 0)
            ->all();
        $products = Product::with('vendor')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($cart as $key => $line) {
            $productId = (int) ($line['product']['id'] ?? $key);
            $product = $products->get($productId);

            if (!$product || !$product->is_active || $product->vendor?->status !== 'approved') {
                $cart[$key]['product']['available'] = false;
                $cart[$key]['product']['image_url'] = asset('assets/images/product-placeholder.svg');
                continue;
            }

            $cart[$key]['product'] = array_merge($line['product'], [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'price' => $product->price,
                'image' => $product->image,
                'image_url' => $product->image_url,
                'vendor' => $product->vendor->store_name,
                'vendor_stall_location' => $product->vendor->stall_location,
                'available' => true,
            ]);
            $cart[$key]['stock'] = $product->stock;
        }

        return $cart;
    }
}
