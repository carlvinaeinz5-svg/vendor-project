@extends('layouts.main')
@section('title', 'Checkout — Nakawa Market')
@section('content')
<div class="page-heading"><div><span class="eyebrow">Final step</span><h1>Checkout</h1></div></div>
<div class="checkout-notice">This prototype records payments manually. No live payment is processed.</div>
<form method="POST" action="{{ route('checkout.place') }}" class="checkout-layout">
@csrf
<div class="checkout-form">
    <section class="form-section"><h3>Contact details</h3>
        <div class="field-grid">
            <div class="field"><label>Full name</label><input name="full_name" value="{{ old('full_name', $customer->name) }}" autocomplete="name" required placeholder="Your full name"></div>
            <div class="field"><label>Phone number</label><input name="phone" value="{{ old('phone', $customer->phone) }}" autocomplete="tel" required placeholder="e.g. 0700 000 000"></div>
        </div>
    </section>
    <section class="form-section"><h3>How would you like to receive your order?</h3>
        <div class="radio-cards fulfillment-options">
            <label>
                <input type="radio" name="fulfillment_method" value="collection" required @checked(old('fulfillment_method') === 'collection')>
                <span><strong>Collect from the vendor</strong><small>Pickup locations are shown below and on your order confirmation.</small></span>
            </label>
            <label>
                <input type="radio" name="fulfillment_method" value="delivery" required @checked(old('fulfillment_method') === 'delivery')>
                <span><strong>Request delivery</strong><small>Enter your delivery location. Confirm arrangements with the vendor.</small></span>
            </label>
        </div>
        @error('fulfillment_method')<small class="field-error">{{ $message }}</small>@enderror
        <div class="field" id="delivery-address-field">
            <label for="delivery-address">Delivery address or area</label>
            <input id="delivery-address" name="delivery_address" value="{{ old('delivery_address') }}" autocomplete="street-address" maxlength="255" placeholder="Area, street, building, and nearby landmark">
            <small>Required for delivery. No delivery fee is calculated here; confirm any delivery cost directly with the vendor.</small>
            @error('delivery_address')<small class="field-error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="order-note">Additional instructions (optional)</label>
            <textarea id="order-note" name="note" rows="3" maxlength="500" placeholder="Add directions or collection details">{{ old('note') }}</textarea>
            @error('note')<small class="field-error">{{ $message }}</small>@enderror
        </div>
    </section>
    <section class="form-section"><h3>Payment method</h3>
        <div class="radio-cards">
            @foreach($paymentMethods as $method => $description)
                <label><input type="radio" name="payment_method" value="{{ $method }}" required @checked(old('payment_method', $customer->preferred_payment_method) === $method)><span><strong>{{ $method }}</strong><small>{{ $description }}</small></span></label>
            @endforeach
        </div>
        @error('payment_method')<small class="field-error">{{ $message }}</small>@enderror
        <p class="secure-note">Payments are arranged directly with each vendor. This prototype does not collect card details or process online transactions.</p>
    </section>
</div>
<aside class="summary-card checkout-summary">
    @error('cart')<div class="alert alert-danger">{{ $message }}</div>@enderror
    <h3>Vendor order parts</h3>
    @foreach($vendorParts as $vendor => $lines)
    <div class="vendor-part">
        <div><strong>{{ $vendor }}</strong><span class="status pending">Pending</span></div>
        <p>Collection point: {{ $lines->first()['product']['vendor_stall_location'] ?: 'Confirm the stall location with the vendor.' }}</p>
        @foreach($lines as $line)<p>{{ $line['quantity'] }}× {{ $line['product']['name'] }}</p>@endforeach
        <b>UGX {{ number_format($lines->sum(fn($line) => $line['product']['price'] * $line['quantity'])) }}</b>
    </div>
    @endforeach
    <div class="summary-total"><span>Order total</span><strong>UGX {{ number_format($total) }}</strong></div>
    <button class="btn btn-primary btn-full" type="submit">Place order</button>
</aside>
</form>
<script>
    (() => {
        const addressField = document.getElementById('delivery-address-field');
        const addressInput = document.getElementById('delivery-address');
        const choices = document.querySelectorAll('input[name="fulfillment_method"]');

        if (!addressField || !addressInput || choices.length === 0) {
            return;
        }

        const syncAddressField = () => {
            const deliverySelected = document.querySelector('input[name="fulfillment_method"]:checked')?.value === 'delivery';
            addressField.hidden = !deliverySelected;
            addressInput.required = deliverySelected;
        };

        choices.forEach((choice) => choice.addEventListener('change', syncAddressField));
        syncAddressField();
    })();
</script>
@endsection
