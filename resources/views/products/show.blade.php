@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="page-header">
    <h1>{{ $product->name }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Edit</a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>
</div>
<div class="card" style="max-width:600px;">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Type</dt>
            <dd class="col-sm-8"><span class="badge bg-{{ $product->type === 'service' ? 'info' : 'secondary' }}">{{ ucfirst($product->type) }}</span></dd>
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Price</dt>
            <dd class="col-sm-8 fw-600">{{ number_format($product->price, 2) }}</dd>
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Tax Rate</dt>
            <dd class="col-sm-8">{{ $product->tax_rate ? $product->tax_rate . '%' : 'N/A' }}</dd>
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">SKU</dt>
            <dd class="col-sm-8">{{ $product->sku ?? 'N/A' }}</dd>
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Status</dt>
            <dd class="col-sm-8"><span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($product->status) }}</span></dd>
            @if($product->description)
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Description</dt>
            <dd class="col-sm-8">{{ $product->description }}</dd>
            @endif
        </dl>
    </div>
</div>
@endsection
