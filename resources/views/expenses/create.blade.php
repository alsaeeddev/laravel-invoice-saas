@extends('layouts.app')
@section('title', 'Record Expense')
@section('content')
<div class="page-header">
    <h1>Record Expense</h1>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="card" style="max-width:720px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amount *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                    </div>
                    @error('amount')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date *</label>
                    <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <input type="text" name="payment_method" class="form-control" value="{{ old('payment_method') }}" placeholder="Cash, Card...">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Receipt (PDF/Image, max 5MB)</label>
                    <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    @error('receipt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
