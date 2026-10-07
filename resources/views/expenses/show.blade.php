@extends('layouts.app')
@section('title', $expense->title)
@section('content')
<div class="page-header">
    <h1>{{ $expense->title }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Edit</a>
        @if($expense->receipt_path)
            <a href="{{ route('expenses.receipt', $expense) }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i> Receipt</a>
        @endif
    </div>
</div>
<div class="card" style="max-width:600px;">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Amount</dt>
            <dd class="col-sm-8 fw-700 fs-5">{{ number_format($expense->amount, 2) }}</dd>
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Date</dt>
            <dd class="col-sm-8">{{ $expense->expense_date->format('M d, Y') }}</dd>
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Category</dt>
            <dd class="col-sm-8">{{ $expense->category?->name ?? '—' }}</dd>
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Method</dt>
            <dd class="col-sm-8">{{ $expense->payment_method ?? '—' }}</dd>
            @if($expense->notes)
            <dt class="col-sm-4 text-muted" style="font-size:0.82rem;">Notes</dt>
            <dd class="col-sm-8">{{ $expense->notes }}</dd>
            @endif
        </dl>
    </div>
</div>
@endsection
