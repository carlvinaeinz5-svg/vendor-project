@extends('layouts.main')
@section('title', 'Vendor Dashboard — Nakawa Market')
@section('content')
<div class="dashboard">
    <aside class="sidebar">
        <strong>Vendor workspace</strong>
        <a class="active" href="{{ route('vendor.dashboard') }}">Overview</a>
        <a href="#products">My products</a>
        <a href="#orders">Orders</a>
        <a href="#payments">Cash payments</a>
    </aside>

    <section>
        <div class="page-heading"><div><span class="eyebrow">{{ $vendor->store_name }}</span><h1>Store overview</h1></div></div>
        <div class="vendor-market-info">
            <div><span>Goods category</span><strong>{{ $vendor->category->name }}</strong><small>Your products are listed under this category.</small></div>
            <div><span>Stall number / location</span><strong>{{ $vendor->stall_location ?: 'Not assigned yet' }}</strong><small>Stall positions are assigned by the market administrator.</small></div>
        </div>
        <div class="stats">
            <div><span>Products</span><strong>{{ $products->count() }}</strong></div>
            <div><span>Pending orders</span><strong>{{ $items->where('status','pending')->count() }}</strong></div>
            <div><span>Completed sales</span><strong>UGX {{ number_format($items->where('status','completed')->sum('subtotal')) }}</strong></div>
            <div><span>Stock alerts</span><strong>{{ $products->where('stock','<=',5)->count() }}</strong></div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger"><strong>Please check the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="dashboard-card product-create-card" id="products">
            <h2>Add a product</h2>
            <p class="allocation-help">New listings are placed in your assigned goods category: {{ $vendor->category->name }}.</p>
            <form class="product-form" method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="field"><label for="new-product-name">Product name</label><input id="new-product-name" name="name" value="{{ old('name') }}" required maxlength="150"></div>
                <div class="field"><label for="new-product-unit">Unit / size</label><input id="new-product-unit" name="unit" value="{{ old('unit', 'item') }}" required maxlength="30" placeholder="e.g. 1 kg, one bunch"></div>
                <div class="field"><label for="new-product-price">Price (UGX)</label><input id="new-product-price" type="number" name="price" value="{{ old('price') }}" min="1" step="1" required></div>
                <div class="field"><label for="new-product-stock">Stock</label><input id="new-product-stock" type="number" name="stock" value="{{ old('stock', 0) }}" min="0" step="1" required></div>
                <div class="field product-description-field"><label for="new-product-description">Description</label><textarea id="new-product-description" name="description" rows="2" maxlength="2000">{{ old('description') }}</textarea></div>
                <div class="field"><label for="new-product-image">Product photo</label><input id="new-product-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, or WebP; maximum 5 MB.</small></div>
                <button class="btn btn-primary" type="submit">Add product</button>
            </form>
        </section>

        <section class="dashboard-card product-list-card">
            <h2>Your product listings</h2>
            <div class="allocation-table-wrap">
                <table>
                    <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Listing</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td><div class="vendor-product-cell"><img src="{{ $product->image_url }}" alt=""><span>{{ $product->name }}<small>{{ $product->unit }} · {{ $product->category->name }}</small></span></div></td>
                            <td>UGX {{ number_format($product->price) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td><span class="status {{ $product->is_active ? 'confirmed' : 'cancelled' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span></td>
                            <td>
                                <details class="product-edit">
                                    <summary class="btn btn-outline btn-sm">Edit</summary>
                                    <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <label>Product name<input name="name" value="{{ $product->name }}" maxlength="150" required></label>
                                        <label>Unit / size<input name="unit" value="{{ $product->unit }}" maxlength="30" required></label>
                                        <label>Price (UGX)<input type="number" name="price" value="{{ $product->price }}" min="1" required></label>
                                        <label>Stock<input type="number" name="stock" value="{{ $product->stock }}" min="0" required></label>
                                        <label>Description<textarea name="description" rows="2" maxlength="2000">{{ $product->description }}</textarea></label>
                                        <label>Replace photo<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
                                        <button class="btn btn-primary btn-sm" type="submit">Save changes</button>
                                    </form>
                                </details>
                                <form class="product-delete-form" method="POST" action="{{ route('vendor.products.destroy', $product) }}" onsubmit="return confirm('Remove this product from your store?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5">You have not added any products yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-card" id="orders">
            <h2>Recent orders</h2>
            <div class="allocation-table-wrap">
                <table>
                    <thead><tr><th>Order</th><th>Items</th><th>Fulfillment</th><th>Amount</th><th>Status</th><th>Update</th></tr></thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->order->number }}</td>
                            <td>{{ $item->product->name }} × {{ $item->quantity }}</td>
                            <td>
                                <strong>{{ $item->order->fulfillmentLabel() }}</strong>
                                @if($item->order->fulfillment_method === 'delivery')
                                    <small>{{ $item->order->delivery_address }}</small>
                                @else
                                    <small>Pickup at {{ $vendor->stall_location ?: 'your stall' }}</small>
                                @endif
                                <small>Customer: {{ $item->order->customer_name }} · {{ $item->order->phone }}</small>
                                @if($item->order->delivery_note)<small>Instructions: {{ $item->order->delivery_note }}</small>@endif
                            </td>
                            <td>UGX {{ number_format($item->subtotal) }}</td>
                            <td><span class="status {{ in_array($item->status, ['confirmed', 'ready', 'completed']) ? 'confirmed' : ($item->status === 'pending' ? 'pending' : 'cancelled') }}">{{ $item->statusLabelFor($item->order->fulfillment_method) }}</span></td>
                            <td><form method="POST" action="{{ route('vendor.order.status', $item) }}">@csrf @method('PATCH')<select name="status" aria-label="Order item status"><option value="pending" @selected($item->status==='pending')>Pending</option><option value="confirmed" @selected($item->status==='confirmed')>Confirm</option><option value="ready" @selected($item->status==='ready')>Ready for {{ $item->order->handoffLabel() }}</option><option value="completed" @selected($item->status==='completed')>{{ $item->order->fulfillment_method === 'delivery' ? 'Delivered' : 'Collected' }}</option><option value="unavailable" @selected($item->status==='unavailable')>Unavailable</option><option value="cancelled" @selected($item->status==='cancelled')>Cancel</option></select><button class="btn btn-primary btn-sm" type="submit">Update</button></form></td>
                        </tr>
                        @empty
                        <tr><td colspan="6">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-card" id="payments">
            <h2>Payments</h2>
            <p class="allocation-help">Confirm a payment only after you have verified it directly with the customer. Payments are not processed by this site.</p>
            <div class="allocation-table-wrap">
                <table>
                    <thead><tr><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->order->number }}</td>
                            <td>{{ $payment->order->customer_name }}</td>
                            <td>{{ $payment->method }}</td>
                            <td>UGX {{ number_format($payment->amount) }}</td>
                            <td><span class="status {{ $payment->status === 'confirmed' ? 'confirmed' : 'pending' }}">{{ $payment->status === 'confirmed' ? 'Received' : 'Due' }}</span></td>
                            <td>
                                @if($payment->status !== 'confirmed')
                                <form method="POST" action="{{ route('vendor.payment.confirm', $payment) }}">@csrf @method('PATCH')<button class="btn btn-primary btn-sm" type="submit">Mark received</button></form>
                                @else
                                <span>{{ $payment->confirmed_at?->format('d M Y') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">No payments to track yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</div>
@endsection
