@extends('layouts.main')
@section('title', 'Login — Nakawa Market')
@section('content')
<section class="auth-card">
    <span class="eyebrow">Welcome back</span><h1>Sign in</h1><p>Access your customer, vendor or administrator account.</p>
    <form method="POST" action="{{ route('login.post') }}">@csrf
        <div class="field"><label>Email</label><input name="email" type="email" value="{{ old('email') }}" required placeholder="name@example.com"></div>
        <div class="field"><label>Password</label><input name="password" type="password" required placeholder="••••••••"></div>
        @error('email')<div class="alert alert-danger">{{ $message }}</div>@enderror
        <button class="btn btn-primary btn-full" type="submit">Sign in</button>
    </form>
    <div class="auth-links"><a href="{{ route('register') }}">Create an account</a><a href="{{ route('marketplace') }}">Continue shopping</a></div>
</section>
@endsection
