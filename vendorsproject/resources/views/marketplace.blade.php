@extends('layouts.main')
@section('title', 'Marketplace — Nakawa Market')
@section('content')
<section class="hero">
    <div>
        <span class="eyebrow">Nakawa Market, Kampala</span>
        <h1>Everything you need,<br><em>from one market.</em></h1>
        <p>Shop fresh produce, household goods and textiles from independent Nakawa vendors.</p>
    </div>
    <div class="hero-note">
        <span class="hero-note-icon">✓</span>
        <div><strong>One easy checkout</strong><small>Items are sent to each vendor separately</small></div>
    </div>
</section>

<form class="filter-bar" method="GET" action="{{ route('marketplace') }}">
    <div class="search-field"><span>⌕</span><input name="search" value="{{ request('search') }}" placeholder="Search products or vendors"></div>
    <select name="category" onchange="this.form.submit()">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->name }}" @selected(request('category') === $category->name)>{{ $category->name }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary" type="submit">Search</button>
</form>

<div class="section-heading">
    <div><span class="eyebrow">Shop the market</span><h2>Featured products</h2></div>
    <span>{{ $products->count() }} products</span>
</div>

<div class="product-grid">
    @foreach($products as $product)
    <article class="product-card">
        <a class="product-image" href="{{ route('product.show', $product['id']) }}">
            <img src="{{ $product->image_url }}" alt="{{ $product['name'] }}">
            <span class="category-tag">{{ $product->category->name }}</span>
        </a>
        <div class="product-body">
            <span class="vendor-name">{{ $product->vendor->store_name }}</span>
            <h3><a href="{{ route('product.show', $product->id) }}">{{ $product->name }}</a></h3>
            <p>{{ $product->unit }}</p>
            <div class="product-footer">
                <strong>UGX {{ number_format($product->price) }}</strong>
                <form method="POST" action="{{ route('cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button class="icon-btn" type="submit" aria-label="Add {{ $product->name }} to cart">+</button>
                </form>
            </div>
        </div>
    </article>
    @endforeach
</div>
@endsection
