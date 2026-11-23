@extends('layouts.main')
@section('title', 'Order '.$order->number.' — Nakawa Market')
@section('content')
<section class="confirmation-hero">
    <span class="confirmation-mark" aria-hidden="true">✓</span>
    <span class="eyebrow">Order confirmed</span>
    <h1>Thank you for shopping locally.</h1>
    <p>Your order <strong>{{ $order->number }}</strong> has been sent to the vendors for {{ strtolower($order->fulfillmentLabel()) }}. Arrange payment directly with each vendor using your selected method.</p>
    <div class="confirmation-actions">
        <a class="btn btn-primary" href="{{ route('orders') }}">View my orders</a>
        <a class="btn btn-outline" href="{{ route('marketplace') }}">Continue shopping</a>
    </div>
</section>

<div class="confirmation-layout">
    <section class="dashboard-card">
        <h2>Order items</h2>
        @foreach($order->items as $item)
        <div class="confirmation-item">
            <div>
                <strong>{{ $item->product->name }}</strong>
                <small>{{ $item->quantity }} × UGX {{ number_format($item->unit_price) }} · {{ $item->vendor->store_name }}</small>
                <small>
                    @if($order->fulfillment_method === 'collection')
                        Pickup at {{ $item->vendor->stall_location ?: 'stall location to be confirmed' }}
                    @else
                        {{ $order->delivery_address }}
                    @endif
                </small>
                <span class="status {{ in_array($item->status, ['confirmed', 'ready', 'completed']) ? 'confirmed' : ($item->status === 'pending' ? 'pending' : 'cancelled') }}">{{ $item->statusLabelFor($order->fulfillment_method) }}</span>
            </div>
            <strong>UGX {{ number_format($item->subtotal) }}</strong>
        </div>
        @endforeach
        <div class="summary-total"><span>Order total</span><strong>UGX {{ number_format($order->total) }}</strong></div>
    </section>

    <aside class="summary-card">
        <h2>Payment and delivery</h2>
        <div class="summary-line"><span>Payment method</span><strong>{{ $order->payment_method }}</strong></div>
        <div class="summary-line"><span>Fulfillment</span><strong>{{ $order->fulfillmentLabel() }}</strong></div>
        <div class="summary-line"><span>Contact phone</span><strong>{{ $order->phone }}</strong></div>
        @if($order->fulfillment_method === 'delivery')
            <div class="summary-line"><span>Delivery address</span><strong>{{ $order->delivery_address }}</strong></div>
            <p class="secure-note">Confirm delivery timing and any delivery cost directly with each vendor. No delivery fee is calculated by this site.</p>
        @else
            <div class="vendor-part">
                <strong>Collection locations</strong>
                @foreach($order->items->unique('vendor_id') as $item)
                    <p>{{ $item->vendor->store_name }}: {{ $item->vendor->stall_location ?: 'Confirm the stall location with the vendor.' }}</p>
                @endforeach
            </div>
        @endif
        @if($order->delivery_note)
            <div class="summary-line"><span>Instructions</span><strong>{{ $order->delivery_note }}</strong></div>
        @endif
        @php($confirmedPayments = $order->payments->where('status', 'confirmed')->count())
        @if($confirmedPayments === $order->payments->count() && $order->payments->isNotEmpty())
            <div class="summary-line"><span>Payment status</span><span class="status confirmed">Received</span></div>
        @elseif($confirmedPayments > 0)
            <div class="summary-line"><span>Payment status</span><span class="status pending">Partially received</span></div>
        @else
            <div class="summary-line"><span>Payment status</span><span class="status pending">Due to vendors</span></div>
        @endif
        <p class="secure-note">Arrange payment directly with each vendor using {{ $order->payment_method }}. This site does not process transactions.</p>
    </aside>
</div>
@endsection
