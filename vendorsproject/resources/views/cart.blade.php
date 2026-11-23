@extends('layouts.main')
@section('title', 'Your Cart — Nakawa Market')
@section('content')
<div class="page-heading"><div><span class="eyebrow">Shopping basket</span><h1>Your cart</h1></div><span class="muted">{{ count($cart) }} item types</span></div>
@if(empty($cart))
    <div class="empty-state"><div>🛒</div><h2>Your cart is empty</h2><p>Browse the marketplace and add products from any vendor.</p><a class="btn btn-primary" href="{{ route('marketplace') }}">Browse marketplace</a></div>
@else
<div class="cart-layout">
    <div class="cart-table">
        <div class="cart-header"><span>Product</span><span>Vendor</span><span>Quantity</span><span>Subtotal</span><span></span></div>
        @foreach($cart as $line)
        <div class="cart-row">
            <div class="cart-product"><img src="{{ $line['product']['image_url'] ?? asset('assets/images/'.$line['product']['image']) }}" alt="{{ $line['product']['name'] }}"><div><strong>{{ $line['product']['name'] }}</strong><small>{{ $line['product']['unit'] }}</small>@if(empty($line['product']['available']))<small class="field-error">No longer available</small>@elseif($line['quantity'] > ($line['stock'] ?? 99))<small class="field-error">Only {{ $line['stock'] }} available</small>@endif</div></div>
            <span>{{ $line['product']['vendor'] }}</span>
            <form class="qty-form" method="POST" action="{{ route('cart.update') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $line['product']['id'] }}">
                <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="99" onchange="this.form.submit()">
            </form>
            <strong>UGX {{ number_format($line['product']['price'] * $line['quantity']) }}</strong>
            <form method="POST" action="{{ route('cart.remove') }}">@csrf<input type="hidden" name="product_id" value="{{ $line['product']['id'] }}"><button class="remove-btn" type="submit">×</button></form>
        </div>
        @endforeach
    </div>
    <aside class="summary-card">
        <h3>Order summary</h3>
        <div class="summary-line"><span>Subtotal</span><strong>UGX {{ number_format($total) }}</strong></div>
        <div class="summary-line"><span>Delivery</span><span>To be arranged</span></div>
        <div class="summary-total"><span>Total</span><strong>UGX {{ number_format($total) }}</strong></div>
        @if($canCheckout)
            <a class="btn btn-primary btn-full" href="{{ route('checkout') }}">Proceed to checkout</a>
        @else
            <p class="checkout-notice">Please remove unavailable items or adjust quantities before checkout.</p>
        @endif
        <p class="secure-note">✓ You will pay vendors directly</p>
    </aside>
</div>
@endif
@endsection
