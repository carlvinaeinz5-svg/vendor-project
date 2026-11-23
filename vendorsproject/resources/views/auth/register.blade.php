@extends('layouts.main')
@section('title', 'Sign up — Nakawa Market')
@section('content')
<section class="auth-card wide">
    <span class="eyebrow">Join Nakawa Market</span>
    <h1>Create your account</h1>
    <p>Choose how you want to use the marketplace. Vendor accounts are reviewed before their products can be listed.</p>

    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <fieldset class="signup-role-options">
            <legend>Choose an account type</legend>
            <div class="radio-cards">
                <label for="role-customer">
                    <input id="role-customer" type="radio" name="role" value="customer" @checked($defaultRole === 'customer') required>
                    <span><strong>Customer</strong><small>Browse products and place orders.</small></span>
                </label>
                <label for="role-vendor">
                    <input id="role-vendor" type="radio" name="role" value="vendor" @checked($defaultRole === 'vendor') required>
                    <span><strong>Vendor</strong><small>Apply to sell products on the marketplace.</small></span>
                </label>
            </div>
            @error('role')<small class="field-error">{{ $message }}</small>@enderror
        </fieldset>

        <div class="field-grid">
            <div class="field">
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" value="{{ old('full_name') }}" autocomplete="name" maxlength="100" required>
                @error('full_name')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="phone">Phone number <span id="phone-required-note">(optional for customers)</span></label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="30">
                @error('phone')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
                @error('email')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                @error('password')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
        </div>

        <fieldset class="vendor-signup-fields" id="vendor-signup-fields">
            <legend>Vendor application details</legend>
            <p>Complete these fields if you are signing up as a vendor.</p>
            <div class="field-grid">
                <div class="field">
                    <label for="store_name">Store name</label>
                    <input id="store_name" name="store_name" value="{{ old('store_name') }}" maxlength="150">
                    @error('store_name')<small class="field-error">{{ $message }}</small>@enderror
                </div>
                <div class="field">
                    <label for="category_id">Goods category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Choose a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<small class="field-error">{{ $message }}</small>@enderror
                </div>
            </div>
        </fieldset>

        <button class="btn btn-primary btn-full" type="submit">Create account</button>
    </form>
    <div class="auth-links"><a href="{{ route('login') }}">Already have an account? Sign in</a><a href="{{ route('marketplace') }}">Continue shopping</a></div>
</section>
<script>
    (() => {
        const roleOptions = document.querySelectorAll('input[name="role"]');
        const vendorFields = document.getElementById('vendor-signup-fields');
        const phoneInput = document.getElementById('phone');
        const phoneRequiredNote = document.getElementById('phone-required-note');

        if (!vendorFields || !phoneInput || roleOptions.length === 0) {
            return;
        }

        const syncSignupFields = () => {
            const isVendor = document.querySelector('input[name="role"]:checked')?.value === 'vendor';
            vendorFields.hidden = !isVendor;
            vendorFields.querySelectorAll('input, select').forEach((input) => {
                input.required = isVendor;
            });
            phoneInput.required = isVendor;
            phoneRequiredNote.hidden = isVendor;
        };

        roleOptions.forEach((option) => option.addEventListener('change', syncSignupFields));
        syncSignupFields();
    })();
</script>
@endsection
