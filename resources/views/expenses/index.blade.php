@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="page-header">
    <h1>Expenses</h1>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Record Expense</a>
</div>
<div class="card">
    <div class="card-body">
        @if($expenses->isEmpty())
            <div class="empty-state">
                <i class="bi bi-cash-stack"></i>
                <h5>No expenses yet</h5>
                <p>Start tracking your business expenses.</p>
                <a href="{{ route('expenses.create') }}" class="btn btn-primary">Record Expense</a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Title</th><th>Category</th><th>Date</th><th>Amount</th><th>Method</th><th>Receipt</th><th></th></tr></thead>
                <tbody>
                    @foreach($expenses as $expense)
                    <tr>
                        <td><a href="{{ route('expenses.show', $expense) }}" class="text-decoration-none fw-600" style="color:var(--primary);">{{ $expense->title }}</a></td>
                        <td>{{ $expense->category?->name ?? '—' }}</td>
                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td class="fw-600">{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->payment_method ?? '—' }}</td>
                        <td>
                            @if($expense->receipt_path)
                                <a href="{{ route('expenses.receipt', $expense) }}" class="btn btn-xs btn-outline-secondary" style="font-size:0.75rem;padding:2px 8px;"><i class="bi bi-download"></i></a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete?')">
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
        <div class="mt-3">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
@endsection
