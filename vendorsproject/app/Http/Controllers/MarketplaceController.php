<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function marketplace(Request $request)
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->with(['vendor', 'category']);
        if ($request->filled('search')) {
            $search = $request->string('search');
            $products->where(fn ($query) => $query->where('name','like',"%{$search}%")->orWhereHas('vendor', fn ($vendor) => $vendor->where('store_name','like',"%{$search}%")));
        }
        if ($request->filled('category') && $request->category !== 'All categories') {
            $category = $request->string('category');
            $products->whereHas('category', fn ($query) => $query->where('name',$category));
        }
        return view('marketplace', [
            'products' => $products->latest()->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function product(int $id)
    {
        $product = Product::where('is_active', true)
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->with(['vendor.category', 'category'])
            ->findOrFail($id);
        return view('product', compact('product'));
    }
}
