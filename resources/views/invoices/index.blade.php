@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<div class="page-header">
    <h1>Invoices</h1>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New Invoice</a>
</div>

@php $business = auth()->user()->currentBusiness(); @endphp

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px;" placeholder="Search invoice # or customer..." value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="max-width:160px;">
                <option value="">All Statuses</option>
                @foreach(['draft','sent','partially_paid','paid','overdue','cancelled', 'partially_refunded', 'refunded'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-search me-1"></i>Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif

            @if($business && auth()->user()->isOwnerOf($business))
                <div class="ms-auto d-flex gap-2">
                    <button type="button" id="bulkDeleteBtn" class="btn btn-sm btn-outline-danger" disabled>
                        <i class="bi bi-trash me-1"></i>Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                    <button type="button" id="deleteAllBtn" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash3 me-1"></i>Delete All
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($invoices->isEmpty())
            <div class="empty-state">
                <i class="bi bi-file-earmark-text"></i>
                <h5>No invoices yet</h5>
                <p>Create your first invoice to get started.</p>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">Create Invoice</a>
            </div>
        @else
        <form id="bulkDeleteForm" method="POST" action="{{ route('invoices.bulkDestroy') }}">
            @csrf
            @method('DELETE')
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            @if($business && auth()->user()->isOwnerOf($business))
                                <th style="width:36px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                            @endif
                            <th>Invoice #</th><th>Customer</th><th>Date</th><th>Due</th>
                            <th>Total</th><th>Paid</th><th>Status</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        @php $colors=['draft'=>'secondary','sent'=>'primary','partially_paid'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark','partially_refunded'=>'info','refunded'=>'dark']; @endphp
                        <tr>
                            @if($business && auth()->user()->isOwnerOf($business))
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" name="invoice_ids[]" value="{{ $invoice->id }}">
                                </td>
                            @endif
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none fw-600" style="color:var(--primary);">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->customer->name }}</td>
                            <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                            <td class="{{ $invoice->status==='overdue' ? 'text-danger fw-600' : '' }}">{{ $invoice->due_date->format('M d, Y') }}</td>
                            <td>{{ number_format($invoice->total, 2) }}</td>
                            <td>{{ number_format($invoice->amount_paid, 2) }}</td>
                            <td><span class="badge bg-{{ $colors[$invoice->status] ?? 'secondary' }}">{{ ucwords(str_replace('_',' ',$invoice->status)) }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
        <div class="mt-3">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>

@if($business && auth()->user()->isOwnerOf($business))
<!-- Delete All (protected form) -->
<form id="deleteAllForm" method="POST" action="{{ route('invoices.destroyAll') }}" class="d-none">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');
    const bulkForm = document.getElementById('bulkDeleteForm');
    const deleteAllBtn = document.getElementById('deleteAllBtn');
    const deleteAllForm = document.getElementById('deleteAllForm');

    function updateBulkState() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        selectedCount.textContent = checked;
        bulkBtn.disabled = checked === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkState();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (!cb.checked) selectAll.checked = false;
            updateBulkState();
        });
    });

    bulkBtn?.addEventListener('click', function () {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        if (checked === 0) return;
        if (confirm(`Delete ${checked} selected invoice(s)? This cannot be undone.`)) {
            bulkForm.submit();
        }
    });

    deleteAllBtn?.addEventListener('click', function () {
        if (confirm('Are you sure you want to delete ALL invoices? This action cannot be undone.')) {
            if (confirm('This is permanent and cannot be reversed. Proceed?')) {
                deleteAllForm.submit();
            }
        }
    });
});
</script>
@endpush
@endif
@endsection