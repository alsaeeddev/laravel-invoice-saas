@extends('layouts.app')
@section('title', 'Edit Expense')
@section('content')
<div class="page-header">
    <h1>Edit Expense</h1>
    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="card" style="max-width:720px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $expense->title) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amount *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control" value="{{ old('amount', $expense->amount) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date *</label>
                    <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <input type="text" name="payment_method" class="form-control" value="{{ old('payment_method', $expense->payment_method) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Receipt (leave blank to keep existing)</label>
                    @if($expense->receipt_path)
                        <div class="mb-1"><a href="{{ route('expenses.receipt', $expense) }}" class="btn btn-xs btn-outline-secondary" style="font-size:0.8rem;"><i class="bi bi-download me-1"></i>Download Current</a></div>
                    @endif
                    <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update Expense</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
