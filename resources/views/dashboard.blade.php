@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
        <p class="text-muted mb-0" style="font-size:0.85rem;">Here's what's happening with {{ $business->name }} today.</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Invoice
    </a>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Total Revenue</div>
                        <h3 class="mt-1 mb-0 fw-800" style="font-size:1.6rem;font-weight:800;">{{ $business->currency }} {{ number_format($stats['totalRevenue'], 2) }}</h3>
                    </div>
                    <div class="stat-icon" style="background:rgba(99,102,241,0.12);color:#6366f1;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <div style="font-size:0.78rem;color:#64748b;">From paid invoices</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Paid Invoices</div>
                        <h3 class="mt-1 mb-0 fw-800" style="font-size:1.6rem;font-weight:800;">{{ $stats['paidCount'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:#10b981;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <div style="font-size:0.78rem;color:#64748b;">{{ $stats['unpaidCount'] }} unpaid</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Overdue</div>
                        <h3 class="mt-1 mb-0 fw-800" style="font-size:1.6rem;font-weight:800;">{{ $stats['overdueCount'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background:rgba(239,68,68,0.12);color:#ef4444;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <div style="font-size:0.78rem;color:#64748b;">Require attention</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Customers</div>
                        <h3 class="mt-1 mb-0 fw-800" style="font-size:1.6rem;font-weight:800;">{{ $stats['totalCustomers'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background:rgba(14,165,233,0.12);color:#0ea5e9;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div style="font-size:0.78rem;color:#64748b;">{{ $business->currency }} {{ number_format($stats['totalExpenses'], 2) }} in expenses</div>
            </div>
        </div>
    </div>
</div>

<!-- Chart + Recent Invoices -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="fw-700 mb-3" style="font-size:0.9rem;">Revenue Overview (Last 12 Months)</h6>
                <div style="position:relative;height:260px;width:100%;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="fw-700 mb-3" style="font-size:0.9rem;">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Create Invoice
                    </a>
                    <a href="{{ route('customers.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-person-plus me-1"></i> Add Customer
                    </a>
                    <a href="{{ route('expenses.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-cash-coin me-1"></i> Record Expense
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-seam me-1"></i> Add Product
                    </a>
                    <hr>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-bar-chart me-1"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Invoices + Payments -->
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-700 mb-0" style="font-size:0.9rem;">Recent Invoices</h6>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">View All</a>
                </div>
                @if($stats['recentInvoices']->isEmpty())
                    <div class="empty-state py-3">
                        <i class="bi bi-file-earmark-text"></i>
                        <p class="mb-0 mt-2">No invoices yet</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recentInvoices'] as $inv)
                                <tr>
                                    <td><a href="{{ route('invoices.show', $inv->id) }}" class="text-decoration-none fw-600" style="color:var(--primary);">{{ $inv->invoice_number }}</a></td>
                                    <td>{{ $inv->customer_name }}</td>
                                    <td>{{ $business->currency }} {{ number_format($inv->total, 2) }}</td>
                                    <td>
                                        @php $colors = ['draft'=>'secondary','sent'=>'primary','partially_paid'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark']; @endphp
                                        <span class="badge bg-{{ $colors[$inv->status] ?? 'secondary' }}">{{ str_replace('_', ' ', $inv->status) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-700 mb-3" style="font-size:0.9rem;">Recent Payments</h6>
                @if($stats['recentPayments']->isEmpty())
                    <div class="empty-state py-3">
                        <i class="bi bi-credit-card"></i>
                        <p class="mb-0 mt-2">No payments yet</p>
                    </div>
                @else
                    @foreach($stats['recentPayments'] as $pay)
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div>
                            <div class="fw-600" style="font-size:0.85rem;">{{ $pay->customer_name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $pay->invoice_number }} · {{ \Carbon\Carbon::parse($pay->payment_date)->format('M d, Y') }}</div>
                        </div>
                        <div class="fw-700 text-success">+{{ $business->currency }} {{ number_format($pay->amount, 2) }}</div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($stats['months']),
        datasets: [{
            label: 'Revenue',
            data: @json($stats['revenues']),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#6366f1',
            pointRadius: 4,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { font: { size: 11 }, callback: v => '$' + v.toLocaleString() }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});
</script>
@endpush
