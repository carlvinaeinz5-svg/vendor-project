@extends('layouts.main')
@section('title', $product['name'].' — Nakawa Market')
@section('content')
<div class="breadcrumbs"><a href="{{ route('marketplace') }}">Marketplace</a><span>›</span><span>{{ $product->category->name }}</span><span>›</span><strong>{{ $product->name }}</strong></div>
<section class="product-detail">
    <div class="detail-image"><img src="{{ $product->image_url }}" alt="{{ $product['name'] }}"></div>
    <div class="detail-info">
        <span class="vendor-name">Sold by {{ $product->vendor->store_name }}</span>
        <h1>{{ $product['name'] }} <span>({{ $product['unit'] }})</span></h1>
        <div class="detail-price">UGX {{ number_format($product['price']) }}</div>
        <p class="detail-description">{{ $product['description'] }} Collected from the vendor's stall or delivered by arrangement.</p>
        <div class="stock"><span></span> In stock: {{ $product['stock'] }} units</div>
        <form method="POST" action="{{ route('cart.add') }}" class="add-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
            <label>Quantity</label>
            <div class="quantity-row">
                <input type="number" name="quantity" value="1" min="1" max="{{ $product['stock'] }}">
                <button class="btn btn-primary" type="submit">Add to cart</button>
            </div>
        </form>
        <div class="delivery-note"><strong>Payment</strong><p>Cash on delivery or collection, or direct mobile money transfer to the vendor.</p></div>
    </div>
</section>
@endsection
