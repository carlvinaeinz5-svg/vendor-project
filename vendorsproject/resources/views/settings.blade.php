@extends('layouts.main')
@section('title', 'Settings — Nakawa Market')
@section('content')
<section class="settings-intro">
    <span class="eyebrow">Your account</span>
    <h1>Settings</h1>
    <p>Manage your profile, password, and marketplace preferences.</p>
</section>

<div class="settings-grid">
    <section class="settings-card">
        <div class="settings-card-heading"><span class="settings-icon">01</span><div><h2>Profile information</h2><p>Keep your account details up to date.</p></div></div>
        <form method="POST" action="{{ route('settings.profile') }}" class="settings-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="profile-image-field">
                @if($user->profile_image_path)
                    <img class="profile-image-preview" src="{{ asset('storage/'.$user->profile_image_path) }}" alt="Your profile picture">
                @else
                    <span class="profile-image-placeholder" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @endif
                <div class="field">
                    <label for="profile_image">Profile picture</label>
                    <input id="profile_image" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                    <small>JPG, PNG, or WebP; maximum 2 MB.</small>
                    @error('profile_image')<small class="field-error">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="field">
                <label for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                @error('name')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                @error('email')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                @error('phone')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <button class="btn btn-primary" type="submit">Save profile</button>
        </form>
    </section>

    <section class="settings-card">
        <div class="settings-card-heading"><span class="settings-icon">02</span><div><h2>Change password</h2><p>Choose a strong password you don't use elsewhere.</p></div></div>
        <form method="POST" action="{{ route('settings.password') }}" class="settings-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="current_password">Current password</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                @error('current_password')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="password">New password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary" type="submit">Update password</button>
        </form>
    </section>

    <section class="settings-card settings-card-wide">
        <div class="settings-card-heading"><span class="settings-icon">03</span><div><h2>Appearance &amp; notifications</h2><p>Make the marketplace feel right for you.</p></div></div>
        <form method="POST" action="{{ route('settings.preferences') }}" class="settings-form preferences-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="theme">Color theme</label>
                <select id="theme" name="theme">
                    <option value="market" @selected(old('theme', $user->theme) === 'market')>Market green</option>
                    <option value="sunrise" @selected(old('theme', $user->theme) === 'sunrise')>Sunrise</option>
                    <option value="ocean" @selected(old('theme', $user->theme) === 'ocean')>Ocean blue</option>
                </select>
                @error('theme')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="preferred_payment_method">Preferred payment method</label>
                <select id="preferred_payment_method" name="preferred_payment_method">
                    @foreach($paymentMethods as $method => $description)
                        <option value="{{ $method }}" @selected(old('preferred_payment_method', $user->preferred_payment_method) === $method)>{{ $method }}</option>
                    @endforeach
                </select>
                @error('preferred_payment_method')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <label class="preference-toggle">
                <input type="checkbox" name="email_notifications" value="1" @checked((bool) old('email_notifications', $user->email_notifications))>
                <span><strong>Email notifications</strong><small>Receive order and payment updates by email when the app has an email service configured.</small></span>
            </label>
            <button class="btn btn-primary" type="submit">Save preferences</button>
        </form>
    </section>
</div>
@endsection
