@extends('layouts.main')
@section('title', 'Manage Categories — Nakawa Market')
@section('content')
<div class="page-heading">
    <div><span class="eyebrow">Administration</span><h1>Goods categories</h1></div>
    <a class="btn btn-outline" href="{{ route('admin.dashboard') }}">Back to dashboard</a>
</div>

<section class="dashboard-card category-create-card">
    <h2>Add a category</h2>
    <form class="category-form" method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        <div class="field"><label for="category-name">Category name</label><input id="category-name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Fresh produce">@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="category-slug">URL slug</label><input id="category-slug" name="slug" value="{{ old('slug') }}" required maxlength="120" pattern="[A-Za-z0-9_-]+" placeholder="e.g. fresh-produce">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</div>
        <button class="btn btn-primary" type="submit">Add category</button>
    </form>
</section>

<section class="dashboard-card category-list-card">
    <h2>Existing categories</h2>
    <div class="allocation-table-wrap">
        <table>
            <thead><tr><th>Category</th><th>Vendor count</th><th>Product count</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>
                        <form class="category-edit-form" method="POST" action="{{ route('admin.categories.update', $category) }}">
                            @csrf @method('PATCH')
                            <input name="name" value="{{ $category->name }}" required maxlength="100" aria-label="Category name">
                            <input name="slug" value="{{ $category->slug }}" required maxlength="120" pattern="[A-Za-z0-9_-]+" aria-label="Category URL slug">
                            <button class="btn btn-primary btn-sm" type="submit">Save</button>
                        </form>
                    </td>
                    <td>{{ $category->vendors_count }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm" type="submit" @disabled($category->vendors_count || $category->products_count)>Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4">No categories have been created.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
