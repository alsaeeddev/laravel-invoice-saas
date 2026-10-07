@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="page-header">
    <h1>Reports</h1>
</div>

<!-- Report type tabs + filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group btn-group-sm me-2">
                @foreach(['revenue'=>'Revenue','expenses'=>'Expenses','profit'=>'Profit','outstanding'=>'Outstanding','customers'=>'By Customer'] as $key => $label)
                    <a href="?type={{ $key }}&from={{ $from }}&to={{ $to }}"
                       class="btn {{ $type === $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
                @endforeach
            </div>
            <input type="hidden" name="type" value="{{ $type }}">
            @if(!in_array($type, ['outstanding']))
                <input type="date" name="from" class="form-control form-control-sm" style="max-width:150px;" value="{{ $from }}">
                <span class="text-muted">to</span>
                <input type="date" name="to" class="form-control form-control-sm" style="max-width:150px;" value="{{ $to }}">
                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-filter me-1"></i>Apply</button>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($type === 'revenue')
            <div class="d-flex justify-content-between mb-3">
                <h6 class="fw-700">Revenue Report</h6>
                <div class="fw-700 text-success">Total: {{ $business->currency }} {{ number_format($data['total'], 2) }}</div>
            </div>
            @if(isset($data['invoices']) && $data['invoices']->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Invoice #</th><th>Customer</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @foreach($data['invoices'] as $inv)
                        <tr>
                            <td><a href="{{ route('invoices.show', $inv->id) }}" class="text-decoration-none" style="color:var(--primary);">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->customer_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('M d, Y') }}</td>
                            <td class="text-end fw-600">{{ number_format($inv->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty-state"><i class="bi bi-bar-chart"></i><p>No paid invoices in this period.</p></div>
            @endif

        @elseif($type === 'expenses')
            <div class="d-flex justify-content-between mb-3">
                <h6 class="fw-700">Expense Report</h6>
                <div class="fw-700 text-danger">Total: {{ $business->currency }} {{ number_format($data['total'], 2) }}</div>
            </div>
            @if(isset($data['expenses']) && $data['expenses']->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Title</th><th>Category</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @foreach($data['expenses'] as $exp)
                        <tr>
                            <td>{{ $exp->title }}</td>
                            <td>{{ $exp->category_name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($exp->expense_date)->format('M d, Y') }}</td>
                            <td class="text-end fw-600">{{ number_format($exp->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty-state"><i class="bi bi-cash-stack"></i><p>No expenses in this period.</p></div>
            @endif

        @elseif($type === 'profit')
            <h6 class="fw-700 mb-4">Profit Overview</h6>
            <div class="row g-3" style="max-width:560px;">
                <div class="col-sm-4">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <div class="text-muted" style="font-size:0.75rem;">Revenue</div>
                            <h5 class="fw-700 text-success">{{ number_format($data['revenue'], 2) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <div class="text-muted" style="font-size:0.75rem;">Expenses</div>
                            <h5 class="fw-700 text-danger">{{ number_format($data['expenses'], 2) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <div class="text-muted" style="font-size:0.75rem;">Net Profit</div>
                            <h5 class="fw-700 {{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($data['profit'], 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($type === 'outstanding')
            <h6 class="fw-700 mb-3">Outstanding Invoices</h6>
            @if($data['invoices']->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Invoice #</th><th>Customer</th><th>Due Date</th><th class="text-end">Total</th><th class="text-end">Balance Due</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($data['invoices'] as $inv)
                        @php $colors=['sent'=>'primary','partially_paid'=>'warning','overdue'=>'danger']; @endphp
                        <tr>
                            <td><a href="{{ route('invoices.show', $inv->id) }}" class="text-decoration-none" style="color:var(--primary);">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->customer_name }}</td>
                            <td class="{{ $inv->status==='overdue' ? 'text-danger fw-600' : '' }}">{{ \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') }}</td>
                            <td class="text-end">{{ number_format($inv->total, 2) }}</td>
                            <td class="text-end fw-700 text-danger">{{ number_format($inv->balance_due, 2) }}</td>
                            <td><span class="badge bg-{{ $colors[$inv->status] ?? 'secondary' }}">{{ ucwords(str_replace('_',' ',$inv->status)) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty-state"><i class="bi bi-check-circle" style="color:#10b981;"></i><h5 class="text-success">All invoices are settled!</h5></div>
            @endif

        @elseif($type === 'customers')
            <h6 class="fw-700 mb-3">Customer Revenue</h6>
            @if($data['customers']->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Customer</th><th>Company</th><th class="text-end">Invoices</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                        @foreach($data['customers'] as $cust)
                        <tr>
                            <td>{{ $cust->name }}</td>
                            <td>{{ $cust->company ?? '—' }}</td>
                            <td class="text-end">{{ $cust->invoice_count }}</td>
                            <td class="text-end fw-700">{{ number_format($cust->total_revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty-state"><i class="bi bi-people"></i><p>No data for this period.</p></div>
            @endif
        @endif
    </div>
</div>
@endsection
