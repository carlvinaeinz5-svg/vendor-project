@extends('layouts.main')
@section('title', 'My Orders — Nakawa Market')
@section('content')
<div class="page-heading"><div><span class="eyebrow">Customer area</span><h1>My orders</h1></div></div>
@if($orders->isEmpty())
    <div class="empty-state"><div>📦</div><h2>No orders yet</h2><p>Your orders will appear here after you checkout.</p><a class="btn btn-primary" href="{{ route('marketplace') }}">Browse marketplace</a></div>
@else
    @foreach($orders as $order)
    <section class="order-card">
        <div class="order-head">
            <div><strong>{{ $order->number }}</strong><small>Placed {{ $order->created_at->format('d M Y') }} · UGX {{ number_format($order->total) }} · {{ $order->fulfillmentLabel() }}</small></div>
            <div class="order-head-actions"><span class="status {{ $order->status === 'completed' ? 'confirmed' : 'pending' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span><a class="btn btn-primary btn-sm" href="{{ route('orders.confirmation', $order) }}">View order</a></div>
        </div>
        <div class="order-parts">
            @foreach($order->items as $item)
            <div class="order-row">
                <div>
                    <strong>{{ $item->vendor->store_name }}</strong>
                    <small>{{ $item->quantity }}× {{ $item->product->name }}</small>
                    @if($order->fulfillment_method === 'collection')
                        <small>Pickup: {{ $item->vendor->stall_location ?: 'confirm stall with vendor' }}</small>
                    @endif
                </div>
                <strong>UGX {{ number_format($item->subtotal) }}</strong>
                <span class="status {{ in_array($item->status, ['confirmed', 'ready', 'completed']) ? 'confirmed' : ($item->status === 'pending' ? 'pending' : 'cancelled') }}">{{ $item->statusLabelFor($order->fulfillment_method) }}</span>
            </div>
            @endforeach
        </div>
    </section>
    @endforeach
@endif
@endsection
