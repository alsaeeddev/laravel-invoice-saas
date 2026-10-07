@extends('layouts.app')
@section('title', $customer->name)
@section('content')
<div class="page-header">
    <h1>{{ $customer->name }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Edit</a>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New Invoice</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-700 mb-3" style="font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">Contact Info</h6>
                @if($customer->company)<p class="mb-1"><i class="bi bi-building me-2 text-muted"></i>{{ $customer->company }}</p>@endif
                @if($customer->email)<p class="mb-1"><i class="bi bi-envelope me-2 text-muted"></i>{{ $customer->email }}</p>@endif
                @if($customer->phone)<p class="mb-1"><i class="bi bi-telephone me-2 text-muted"></i>{{ $customer->phone }}</p>@endif
                @if($customer->city || $customer->country)
                    <p class="mb-1"><i class="bi bi-geo-alt me-2 text-muted"></i>{{ $customer->city }}{{ $customer->city && $customer->country ? ', ' : '' }}{{ $customer->country }}</p>
                @endif
                @if($customer->notes)<p class="mt-2 text-muted" style="font-size:0.85rem;">{{ $customer->notes }}</p>@endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <!-- Financial Summary -->
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <div class="text-muted" style="font-size:0.75rem;">Total Invoiced</div>
                        <h5 class="fw-700 mb-0">{{ number_format($customer->totalInvoiced(), 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <div class="text-muted" style="font-size:0.75rem;">Total Paid</div>
                        <h5 class="fw-700 mb-0 text-success">{{ number_format($customer->totalPaid(), 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <div class="text-muted" style="font-size:0.75rem;">Outstanding</div>
                        <h5 class="fw-700 mb-0 {{ $customer->totalOutstanding() > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($customer->totalOutstanding(), 2) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices -->
        <div class="card">
            <div class="card-body">
                <h6 class="fw-700 mb-3" style="font-size:0.9rem;">Invoices</h6>
                @if($customer->invoices->isEmpty())
                    <div class="empty-state py-2"><i class="bi bi-file-earmark-text"></i><p>No invoices yet</p></div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Date</th><th>Due</th><th>Total</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($customer->invoices as $inv)
                            @php $colors = ['draft'=>'secondary','sent'=>'primary','partially_paid'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark']; @endphp
                            <tr>
                                <td><a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none fw-600" style="color:var(--primary);">{{ $inv->invoice_number }}</a></td>
                                <td>{{ $inv->invoice_date->format('M d, Y') }}</td>
                                <td>{{ $inv->due_date->format('M d, Y') }}</td>
                                <td>{{ number_format($inv->total, 2) }}</td>
                                <td><span class="badge bg-{{ $colors[$inv->status] ?? 'secondary' }}">{{ str_replace('_', ' ', $inv->status) }}</span></td>
                                <td><a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
