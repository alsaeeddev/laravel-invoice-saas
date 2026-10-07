@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="page-header">
    <h1>Edit Product</h1>
    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="card" style="max-width:720px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf @method('PATCH')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-select">
                        <option value="service" {{ old('type', $product->type) === 'service' ? 'selected' : '' }}>Service</option>
                        <option value="product" {{ old('type', $product->type) === 'product' ? 'selected' : '' }}>Product</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="price" step="0.01" min="0" class="form-control" value="{{ old('price', $product->price) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" step="0.01" min="0" max="100" class="form-control" value="{{ old('tax_rate', $product->tax_rate) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
