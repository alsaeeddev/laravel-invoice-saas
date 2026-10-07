@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="page-header">
    <h1>Customers</h1>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Customer</a>
</div>

<div class="card">
    <div class="card-body">
        @if($customers->isEmpty())
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h5>No customers yet</h5>
                <p>Add your first customer to get started.</p>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">Add Customer</a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th><th>Company</th><th>Email</th><th>Phone</th><th>Invoiced</th><th>Outstanding</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none fw-600" style="color:var(--primary);">{{ $customer->name }}</a>
                        </td>
                        <td>{{ $customer->company ?? '—' }}</td>
                        <td>{{ $customer->email ?? '—' }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td>{{ number_format($customer->totalInvoiced(), 2) }}</td>
                        <td class="{{ $customer->totalOutstanding() > 0 ? 'text-danger fw-600' : 'text-success' }}">
                            {{ number_format($customer->totalOutstanding(), 2) }}
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?')">
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
        <div class="mt-3">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection
