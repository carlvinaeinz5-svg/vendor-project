@extends('layouts.main')
@section('title', 'Administrator — Nakawa Market')
@section('content')
<div class="dashboard">
<aside class="sidebar"><strong>Administration</strong><a class="active" href="{{ route('admin.dashboard') }}">Overview</a><a href="{{ route('admin.dashboard') }}#vendor-applications">Vendor applications</a><a href="{{ route('admin.categories') }}">Categories</a></aside>
<section><div class="page-heading"><div><span class="eyebrow">Marketplace</span><h1>Administration</h1></div></div>
<div class="stats"><div><span>All vendors</span><strong>{{ $stats['vendors'] }}</strong></div><div><span>Pending applications</span><strong>{{ $stats['pending'] }}</strong></div><div><span>Active orders</span><strong>{{ $stats['orders'] }}</strong></div><div><span>Products</span><strong>{{ $stats['products'] }}</strong></div></div>
<div class="dashboard-card" id="vendor-applications"><h3>Vendor applications</h3><p class="allocation-help">Assign a unique stall number or location as you review each vendor. Product listings use the vendor's goods category.</p>@error('stall_location')<div class="alert alert-danger">{{ $message }}</div>@enderror<div class="allocation-table-wrap"><table><thead><tr><th>Vendor</th><th>Goods category</th><th>Phone</th><th>Stall number / location</th><th>Status &amp; allocation</th></tr></thead><tbody>
@foreach($applications as $vendor)<tr><td>{{ $vendor->store_name }}</td><td>{{ $vendor->category->name }}</td><td>{{ $vendor->phone }}</td><td>{{ $vendor->stall_location ?: 'Not assigned' }}</td><td><form class="vendor-allocation-form" method="POST" action="{{ route('admin.vendor.status',$vendor) }}">@csrf @method('PATCH')<select name="status" aria-label="Vendor status"><option value="pending" @selected($vendor->status==='pending')>Pending</option><option value="approved" @selected($vendor->status==='approved')>Approve</option><option value="suspended" @selected($vendor->status==='suspended')>Suspend</option></select><input name="stall_location" value="{{ $vendor->stall_location }}" maxlength="255" placeholder="e.g. Lane A, Stall 12" aria-label="Stall number or location"><button class="btn btn-primary btn-sm" type="submit">Save</button></form></td></tr>@endforeach
</tbody></table></div>
</section></div>
@endsection
