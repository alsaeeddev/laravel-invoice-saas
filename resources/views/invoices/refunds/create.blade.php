@extends('layouts.app')
@section('title', 'Issue Refund — '.$invoice->invoice_number)
@section('content')

@php
    $customerEmail = $invoice->customer->email;
    $sendEmail     = session()->hasOldInput() ? (bool) old('send_email') : (bool) $customerEmail;
@endphp

<div class="page-header">
    <h1>Issue Refund</h1>
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="text-muted" style="font-size:0.8rem;">Invoice</div>
                    <div class="fw-700">{{ $invoice->invoice_number }}</div>
                    <div class="text-muted mt-2" style="font-size:0.8rem;">Amount Already Paid</div>
                    <div class="fw-700 text-success">{{ $invoice->business->currency }} {{ number_format($invoice->amount_paid, 2) }}</div>
                </div>

                <form method="POST" action="{{ route('invoices.refunds.store', $invoice) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Refund Amount *</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $invoice->amount_paid }}"
                            name="amount" class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Refund Date *</label>
                        <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                            value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Method *</label>
                        <select name="method" class="form-select @error('method') is-invalid @enderror" required>
                            @foreach(['cash','bank_transfer','card','other'] as $m)
                                <option value="{{ $m }}" {{ old('method') === $m ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_',' ',$m)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes / Reason</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Reason for refund...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
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

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Confirm refund of {{ $invoice->business->currency }} ' + document.querySelector('[name=amount]').value + '?')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Issue Refund
                        </button>
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const amount = document.querySelector('[name=amount]');
    const hint   = document.getElementById('email-hint');
    if (!amount || !hint) return;

    const paid = @json((float) $invoice->amount_paid);

    function update() {
        const v = parseFloat(amount.value) || 0;
        hint.textContent = v >= paid - 0.001
           hint.textContent = v >= paid - 0.001
    ? 'The customer will receive a "Refund issued" (full refund) email.'
    : 'The customer will receive a "Partial refund issued" email with the net paid amount.';
    }

    amount.addEventListener('input', update);
    update();
})();
</script>
@endpush