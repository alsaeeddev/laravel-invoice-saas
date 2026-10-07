@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="page-header">
    <h1>Products & Services</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Product</a>
</div>
<div class="card">
    <div class="card-body">
        @if($products->isEmpty())
            <div class="empty-state">
                <i class="bi bi-box-seam"></i>
                <h5>No products yet</h5>
                <p>Add products or services to use on invoices.</p>
                <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Name</th><th>Type</th><th>SKU</th><th>Price</th><th>Tax Rate</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="text-decoration-none fw-600" style="color:var(--primary);">{{ $product->name }}</a>
                            @if($product->description)<div class="text-muted" style="font-size:0.75rem;">{{ Str::limit($product->description, 50) }}</div>@endif
                        </td>
                        <td><span class="badge bg-{{ $product->type === 'service' ? 'info' : 'secondary' }} text-{{ $product->type === 'service' ? 'white' : '' }}">{{ ucfirst($product->type) }}</span></td>
                        <td>{{ $product->sku ?? '—' }}</td>
                        <td>{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->tax_rate ? $product->tax_rate . '%' : '—' }}</td>
                        <td><span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($product->status) }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
