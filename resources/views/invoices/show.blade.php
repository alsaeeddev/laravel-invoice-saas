@extends('layouts.app')
@section('title', 'Invoice '.$invoice->invoice_number)
@section('content')

@php
    $colors=[
        'draft'=>'secondary','sent'=>'primary','partially_paid'=>'warning','paid'=>'success',
        'overdue'=>'danger','cancelled'=>'dark','partially_refunded'=>'info','refunded'=>'dark',
    ];
    $totalRefunded = $invoice->payments->where('type', 'refund')->sum('amount');
    $customerEmail = $invoice->customer->email;
    $emailFailed   = $invoice->email_status === 'failed' && $invoice->status !== 'draft';
@endphp

@if($emailFailed)
    <div class="alert alert-danger d-flex justify-content-between align-items-center gap-3">
        <div>
            <strong><i class="bi bi-exclamation-triangle me-1"></i> Email failed to send.</strong>
            @if($invoice->email_error)
                <div style="font-size:0.8rem;">{{ $invoice->email_error }}</div>
            @endif
            @unless($customerEmail)
                <div style="font-size:0.8rem;">
                    Customer has no email. <a href="{{ route('customers.edit', $invoice->customer) }}">Add email</a>, then retry.
                </div>
            @endunless
        </div>
        <form method="POST" action="{{ route('invoices.resend', $invoice) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger" @disabled(! $customerEmail)>
                <i class="bi bi-arrow-repeat me-1"></i> Retry
            </button>
        </form>
    </div>
@endif

<div class="page-header">
    <div>
        <h1>Invoice {{ $invoice->invoice_number }}</h1>
        <span class="badge bg-{{ $colors[$invoice->status] ?? 'secondary' }} fs-6">{{ ucwords(str_replace('_',' ',$invoice->status)) }}</span>

        <div class="text-muted mt-2" style="font-size:0.8rem;">
            <div>
                <i class="bi bi-envelope me-1"></i> Last email:
                <span class="badge bg-{{ $invoice->emailStatusColor() }}">{{ $invoice->emailStatusLabel() }}</span>
            </div>

            @if($invoice->emailTypeLabel())
                <div class="mt-1">
                    <i class="bi bi-tag me-1"></i> Type:
                    <span class="fw-600">{{ $invoice->emailTypeLabel() }}</span>
                </div>
            @endif

            @if($invoice->email_status === 'sent' && $invoice->sent_at)
                <div class="mt-1">
                    <i class="bi bi-clock me-1"></i> {{ $invoice->sent_at->format('M d, Y g:i A') }}
                </div>
            @elseif($invoice->email_status === 'queued')
                <div class="mt-1">
                    <i class="bi bi-hourglass-split me-1"></i> Will be sent shortly
                </div>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($invoice->status === 'draft')
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="d-inline" id="form-send">
                @csrf
                <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Send this invoice to {{ addslashes($customerEmail ?? 'the customer') }}?')">
                    <i class="bi bi-send me-1"></i> Send
                </button>
            </form>
        @endif

        @if($invoice->status !== 'draft' && ! $emailFailed)
            <form method="POST" action="{{ route('invoices.resend', $invoice) }}" class="d-inline" id="form-resend"
                onsubmit="return confirm('Email the latest update of this invoice to {{ addslashes($customerEmail ?? 'the customer') }}?')">
                @csrf
                <button type="submit" class="btn btn-outline-primary" @disabled(! $customerEmail)
                    title="{{ $customerEmail ? 'Resend email to '.$customerEmail : 'Customer has no email' }}">
                    <i class="bi bi-envelope-arrow-up me-1"></i> Resend
                </button>
            </form>
        @endif

        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> PDF
        </a>

        @if(!in_array($invoice->status, ['paid', 'cancelled']))
            <a href="{{ route('invoices.payments.create', $invoice) }}" class="btn btn-success">
                <i class="bi bi-cash me-1"></i> Record Payment
            </a>
        @endif

        @if($invoice->amount_paid > 0 && !in_array($invoice->status, ['cancelled', 'refunded']))
            <a href="{{ route('invoices.refunds.create', $invoice) }}" class="btn btn-outline-danger">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Issue Refund
            </a>
        @endif

        @if(!in_array($invoice->status, ['cancelled', 'paid']))
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="bi bi-x-circle me-1"></i> Cancel Invoice
            </button>
        @endif

        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="d-inline" id="form-delete">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"
                onclick="return window.confirm('Permanently delete {{ $invoice->invoice_number }}? This cannot be undone.')">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <!-- Invoice Details -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted fw-600 mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">From</h6>
                        @if($invoice->business->logo_path)
                            <img src="{{ Storage::url($invoice->business->logo_path) }}" height="40" class="mb-2">
                        @endif
                        <div class="fw-700">{{ $invoice->business->name }}</div>
                        @if($invoice->business->email)<div class="text-muted" style="font-size:0.85rem;">{{ $invoice->business->email }}</div>@endif
                        @if($invoice->business->address)<div class="text-muted" style="font-size:0.85rem;">{{ $invoice->business->address }}</div>@endif
                    </div>
                    <div class="col-sm-6">
                        <h6 class="text-muted fw-600 mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Bill To</h6>
                        <div class="fw-700">{{ $invoice->customer->name }}</div>
                        @if($invoice->customer->company)<div>{{ $invoice->customer->company }}</div>@endif
                        @if($invoice->customer->email)<div class="text-muted" style="font-size:0.85rem;">{{ $invoice->customer->email }}</div>@endif
                        @if($invoice->customer->address)<div class="text-muted" style="font-size:0.85rem;">{{ $invoice->customer->address }}</div>@endif
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3">
                        <div class="text-muted" style="font-size:0.75rem;">Invoice #</div>
                        <div class="fw-600">{{ $invoice->invoice_number }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted" style="font-size:0.75rem;">Issue Date</div>
                        <div class="fw-600">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted" style="font-size:0.75rem;">Due Date</div>
                        <div class="fw-600 {{ $invoice->status === 'overdue' ? 'text-danger' : '' }}">{{ $invoice->due_date->format('M d, Y') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted" style="font-size:0.75rem;">Balance Due</div>
                        <div class="fw-700 {{ $invoice->balanceDue() > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $invoice->business->currency }} {{ number_format($invoice->balanceDue(), 2) }}
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table mb-0">
                        <thead><tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Tax</th><th class="text-end">Discount</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-600">{{ $item->description }}</div>
                                    @if($item->product)<div class="text-muted" style="font-size:0.75rem;">{{ $item->product->name }}</div>@endif
                                </td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ $item->tax_rate }}%</td>
                                <td class="text-end">{{ number_format($item->discount_amount, 2) }}</td>
                                <td class="text-end fw-600">{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="5" class="text-end text-muted">Subtotal</td><td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                            @if($invoice->discount_total > 0)
                            <tr><td colspan="5" class="text-end text-muted">Discount</td><td class="text-end text-success">-{{ number_format($invoice->discount_total, 2) }}</td></tr>
                            @endif
                            <tr><td colspan="5" class="text-end text-muted">Tax</td><td class="text-end">{{ number_format($invoice->tax_total, 2) }}</td></tr>
                            <tr>
                                <td colspan="5" class="text-end fw-700">Total</td>
                                <td class="text-end fw-700 fs-5">{{ $invoice->business->currency }} {{ number_format($invoice->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($invoice->notes || $invoice->terms)
                <div class="row">
                    @if($invoice->notes)
                    <div class="col-sm-6">
                        <div class="text-muted fw-600 mb-1" style="font-size:0.75rem;">NOTES</div>
                        <p style="font-size:0.85rem;">{{ $invoice->notes }}</p>
                    </div>
                    @endif
                    @if($invoice->terms)
                    <div class="col-sm-6">
                        <div class="text-muted fw-600 mb-1" style="font-size:0.75rem;">TERMS</div>
                        <p style="font-size:0.85rem;">{{ $invoice->terms }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right: Payments -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-700 mb-0">Payments</h6>
                    @if(!in_array($invoice->status, ['paid', 'cancelled']))
                        <a href="{{ route('invoices.payments.create', $invoice) }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i></a>
                    @endif
                </div>

                @if($invoice->payments->isEmpty())
                    <div class="empty-state py-2" style="font-size:0.85rem;">
                        <i class="bi bi-credit-card" style="font-size:1.5rem;"></i>
                        <p class="mb-0 mt-1">No payments recorded</p>
                    </div>
                @else
                    @foreach($invoice->payments as $payment)
                    @php $isRefund = $payment->type === 'refund'; @endphp
                    <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                        <div>
                            <div class="fw-600 {{ $isRefund ? 'text-danger' : '' }}" style="font-size:0.85rem;">
                                {{ $isRefund ? '−' : '' }}{{ $invoice->business->currency }} {{ number_format($payment->amount, 2) }}
                                @if($isRefund)<span class="badge bg-danger" style="font-size:0.6rem;">REFUND</span>@endif
                            </div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                {{ $payment->payment_date->format('M d, Y') }} · {{ ucwords(str_replace('_',' ',$payment->method)) }}
                            </div>
                            @if($payment->reference_number)
                                <div class="text-muted" style="font-size:0.72rem;">Ref: {{ $payment->reference_number }}</div>
                            @endif
                            @if($isRefund && $payment->notes)
                                <div class="text-muted" style="font-size:0.72rem;">{{ $payment->notes }}</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('invoices.payments.destroy', [$invoice, $payment]) }}"
                            onsubmit="return confirm('{{ $isRefund ? 'Remove this refund entry?' : 'Remove this payment?' }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" style="font-size:0.7rem;padding:2px 6px;"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-between mt-2 fw-700" style="font-size:0.9rem;">
                        <span>Net Paid</span>
                        <span class="text-success">{{ $invoice->business->currency }} {{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    @if($totalRefunded > 0)
                    <div class="d-flex justify-content-between fw-600 text-danger" style="font-size:0.8rem;">
                        <span>Total Refunded</span>
                        <span>−{{ $invoice->business->currency }} {{ number_format($totalRefunded, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->balanceDue() > 0)
                    <div class="d-flex justify-content-between fw-700 text-danger" style="font-size:0.9rem;">
                        <span>Balance Due</span>
                        <span>{{ $invoice->business->currency }} {{ number_format($invoice->balanceDue(), 2) }}</span>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@if(!in_array($invoice->status, ['cancelled', 'paid']))
@push('scripts')
{{-- Placed at the end of the body so it doesn't conflict with the layout's fixed/transform positioning --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel invoice {{ $invoice->invoice_number }}?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Invoice cancelled mark ho jayegi. Yeh action wapas nahi hota.</p>

                @if($invoice->amount_paid > 0)
                    <div class="alert alert-warning" style="font-size:0.875rem;">
                        <strong><i class="bi bi-exclamation-triangle me-1"></i> Customer ne
                        {{ $invoice->business->currency }} {{ number_format($invoice->amount_paid, 2) }} pay kiya hua hai.</strong>
                        <div class="mt-1">Cancel karne se paisa wapas nahi hota, aur cancel ke baad refund ka option bhi nahi milta.
                            Agar refund dena hai to
                            <a href="{{ route('invoices.refunds.create', $invoice) }}" class="alert-link">pehle refund issue karo</a>.
                        </div>
                    </div>
                @endif

                @if($invoice->status !== 'draft')
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" value="1" id="cancel-send-email"
                            {{ $customerEmail ? 'checked' : 'disabled' }}>
                        <label class="form-check-label" for="cancel-send-email">
                            Email customer
                            @if($customerEmail)
                                <span class="text-muted">({{ $customerEmail }})</span>
                            @else
                                <span class="text-muted">— no email on file.</span>
                                <a href="{{ route('customers.edit', $invoice->customer) }}" target="_blank">Add email</a>
                            @endif
                        </label>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep invoice</button>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-x-circle me-1"></i> Cancel Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
@endif