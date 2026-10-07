@extends('layouts.app')
@section('title', 'Record Payment')
@section('content')

@php
    $customerEmail = $invoice->customer->email;
    $sendEmail     = session()->hasOldInput() ? (bool) old('send_email') : (bool) $customerEmail;
@endphp

<div class="page-header">
    <div>
        <h1>Record Payment</h1>
        <p class="text-muted mb-0">Invoice {{ $invoice->invoice_number }} · Balance Due: <strong>{{ number_format($invoice->balanceDue(), 2) }}</strong></p>
    </div>
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Amount *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->balanceDue() }}"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', number_format($invoice->balanceDue(), 2, '.', '')) }}" required>
                    </div>
                    @error('amount')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Date *</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Method *</label>
                    <select name="method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reference #</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" placeholder="Transaction ID, check #...">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" value="1" id="send_email"
                            {{ $customerEmail && $sendEmail ? 'checked' : '' }}
                            {{ $customerEmail ? '' : 'disabled' }}>
                        <label class="form-check-label" for="send_email">
                            Email customer
                            @if($customerEmail)
                                <span class="text-muted">({{ $customerEmail }})</span>
                            @else
                                <span class="text-muted">— no email on file.</span>
                                <a href="{{ route('customers.edit', $invoice->customer) }}" target="_blank">Add email</a>
                                <span class="text-muted">(phir yeh page reload karo)</span>
                            @endif
                        </label>
                    </div>
                    @if($customerEmail)
                        <div class="form-text" id="email-hint"></div>
                    @endif
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Record Payment</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const amount = document.querySelector('[name=amount]');
    const hint   = document.getElementById('email-hint');
    if (!amount || !hint) return;

    const balance = @json($invoice->balanceDue());

    function update() {
        const v = parseFloat(amount.value) || 0;
        hint.textContent = v >= balance - 0.001
? 'The customer will receive a "Payment received – fully paid" email.'
: 'The customer will receive a "Partial payment received" email with the remaining balance.';
    }

    amount.addEventListener('input', update);
    update();
})();
</script>
@endpush