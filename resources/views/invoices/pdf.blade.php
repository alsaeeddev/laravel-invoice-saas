<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #1e293b;
        -webkit-print-color-adjust: exact;
    }
    .page { padding: 45px 50px; max-width: 800px; margin: 0 auto; }

    /* ===== Header ===== */
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    .header-table td { vertical-align: top; padding: 0; border: none; }
    .logo { max-height: 55px; margin-bottom: 8px; }
    .business-name { font-size: 20px; font-weight: bold; color: #4338ca; }
    .business-meta { font-size: 10.5px; color: #64748b; line-height: 1.6; margin-top: 4px; }

    .invoice-title-block { text-align: right; }
    .invoice-title-block h1 {
        font-size: 26px; color: #4338ca; font-weight: bold; letter-spacing: 0.05em;
    }
    .invoice-title-block .number { font-size: 13px; color: #64748b; margin-top: 2px; }

    .status-badge {
        display: inline-block; padding: 5px 14px; border-radius: 20px;
        font-size: 10px; font-weight: bold; text-transform: uppercase;
        letter-spacing: 0.05em; margin-top: 8px;
    }
    .status-paid { background: #dcfce7; color: #166534; }
    .status-sent { background: #dbeafe; color: #1e40af; }
    .status-draft { background: #f1f5f9; color: #475569; }
    .status-partially_paid { background: #fef9c3; color: #854d0e; }
    .status-overdue { background: #fee2e2; color: #991b1b; }
    .status-cancelled { background: #1e293b; color: #ffffff; }

    .header-divider { border-bottom: 3px solid #4338ca; margin-bottom: 28px; }

    /* ===== Bill To / Meta ===== */
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
    .info-table td { vertical-align: top; padding: 0; border: none; }
    .info-box h4 {
        font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.1em;
        color: #94a3b8; margin-bottom: 8px; font-weight: bold;
    }
    .info-box .cust-name { font-size: 13.5px; font-weight: bold; margin-bottom: 3px; }
    .info-box p { font-size: 11.5px; line-height: 1.65; color: #334155; }

    .meta-panel {
        background: #f8fafc; border-radius: 8px; padding: 14px 18px;
        border-left: 3px solid #4338ca;
    }
    .meta-panel table { width: 100%; border-collapse: collapse; }
    .meta-panel td { border: none; padding: 3px 0; font-size: 11.5px; }
    .meta-panel .label { color: #94a3b8; text-transform: uppercase; font-size: 9px; letter-spacing: 0.06em; }
    .meta-panel .value { font-weight: bold; text-align: right; }

    /* ===== Items Table ===== */
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
    .items-table thead th {
        background: #4338ca; color: #fff; padding: 11px 12px;
        font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.05em;
        text-align: left;
    }
    .items-table thead th.text-right { text-align: right; }
    .items-table tbody td {
        padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11.5px;
        vertical-align: top;
    }
    .items-table tbody tr:nth-child(even) { background: #f8fafc; }
    .item-desc-main { font-weight: 600; color: #1e293b; }
    .item-desc-sub { font-size: 10px; color: #94a3b8; margin-top: 2px; }
    .text-right { text-align: right; }

    /* ===== Totals ===== */
    .totals-wrap { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .totals-wrap td { border: none; padding: 0; vertical-align: top; }
    .totals-box { width: 260px; float: right; }
    .totals-box table { width: 100%; border-collapse: collapse; }
    .totals-box td { border: none; padding: 6px 0; font-size: 11.5px; }
    .totals-box .t-label { color: #64748b; }
    .totals-box .t-value { text-align: right; }
    .totals-box .discount-row .t-value { color: #16a34a; }
    .totals-box .grand-row td {
        border-top: 2px solid #4338ca; padding-top: 10px; margin-top: 4px;
        font-size: 15px; font-weight: bold; color: #4338ca;
    }
    .totals-box .paid-row .t-value { color: #16a34a; font-weight: 600; }
    .totals-box .due-row td {
        font-weight: bold; color: #dc2626; border-top: 1px dashed #cbd5e1;
        padding-top: 8px; font-size: 13px;
    }

    /* ===== Footer ===== */
    .clearfix { clear: both; }
    .footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 18px; }
    .footer-table { width: 100%; border-collapse: collapse; }
    .footer-table td { border: none; vertical-align: top; padding: 0; }
    .footer h4 {
        font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.1em;
        color: #94a3b8; margin-bottom: 6px; font-weight: bold;
    }
    .footer p { font-size: 10.5px; color: #475569; line-height: 1.65; }

    .thankyou {
        text-align: center; margin-top: 35px; padding-top: 18px;
        border-top: 1px dashed #cbd5e1; font-size: 10.5px; color: #94a3b8;
    }
</style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width:55%;">
                @if($invoice->business->logo_path)
                    <img src="{{ public_path('storage/'.$invoice->business->logo_path) }}" class="logo" alt="Logo"><br>
                    <div style="font-weight:bold;font-size:15px;margin-top:6px;">{{ $invoice->business->name }}</div>
                @else
                    <div class="business-name">{{ $invoice->business->name }}</div>
                @endif
                <div class="business-meta">
                    @if($invoice->business->email){{ $invoice->business->email }}<br>@endif
                    @if($invoice->business->phone){{ $invoice->business->phone }}<br>@endif
                    @if($invoice->business->address){{ $invoice->business->address }}<br>@endif
                    @if($invoice->business->tax_number)Tax No: {{ $invoice->business->tax_number }}@endif
                </div>
            </td>
            <td style="width:45%;" class="invoice-title-block">
                <h1>INVOICE</h1>
                <div class="number">#{{ $invoice->invoice_number }}</div>
                @php
    $label = $invoice->clientStatusLabel();
    $badgeClass = match ($label) {
        'Paid'           => 'paid',
        'Partially Paid' => 'partially_paid',
        'Unpaid',
        'Overdue'        => 'overdue',
        'Cancelled'      => 'cancelled',
        'Draft',
        'Refunded',
        'Partially Refunded' => 'draft',   // grey badge
        default          => 'sent',        // Unpaid = blue badge
    };
@endphp
<div class="status-badge status-{{ $badgeClass }}">{{ strtoupper($label) }}</div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>

    <!-- Bill To + Meta -->
    <table class="info-table">
        <tr>
            <td style="width:50%;">
                <div class="info-box">
                    <h4>Bill To</h4>
                    <div class="cust-name">{{ $invoice->customer->name }}</div>
                    <p>
                        @if($invoice->customer->company){{ $invoice->customer->company }}<br>@endif
                        @if($invoice->customer->email){{ $invoice->customer->email }}<br>@endif
                        @if($invoice->customer->phone){{ $invoice->customer->phone }}<br>@endif
                        @if($invoice->customer->address){{ $invoice->customer->address }}<br>@endif
                        @if($invoice->customer->city){{ $invoice->customer->city }}@endif
                        @if($invoice->customer->city && $invoice->customer->country), @endif
                        @if($invoice->customer->country){{ $invoice->customer->country }}@endif
                    </p>
                </div>
            </td>
            <td style="width:50%; padding-left:20px;">
                <div class="meta-panel">
                    <table>
                        <tr><td class="label">Issue Date</td><td class="value">{{ $invoice->invoice_date->format('M d, Y') }}</td></tr>
                        <tr><td class="label">Due Date</td><td class="value">{{ $invoice->due_date->format('M d, Y') }}</td></tr>
                        <tr><td class="label">Currency</td><td class="value">{{ $invoice->business->currency }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:38%;">Description</th>
                <th class="text-right" style="width:10%;">Qty</th>
                <th class="text-right" style="width:15%;">Unit Price</th>
                <th class="text-right" style="width:10%;">Tax</th>
                <th class="text-right" style="width:12%;">Discount</th>
                <th class="text-right" style="width:15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    <div class="item-desc-main">{{ $item->description }}</div>
                    @if($item->product)<div class="item-desc-sub">{{ $item->product->name }}</div>@endif
                </td>
                <td class="text-right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ rtrim(rtrim(number_format($item->tax_rate, 2), '0'), '.') }}%</td>
                <td class="text-right">{{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '—' }}</td>
                <td class="text-right"><strong>{{ number_format($item->line_total, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-box">
        <table>
            <tr><td class="t-label">Subtotal</td><td class="t-value">{{ number_format($invoice->subtotal, 2) }}</td></tr>
            @if($invoice->discount_total > 0)
            <tr class="discount-row"><td class="t-label">Discount</td><td class="t-value">-{{ number_format($invoice->discount_total, 2) }}</td></tr>
            @endif
            <tr><td class="t-label">Tax</td><td class="t-value">{{ number_format($invoice->tax_total, 2) }}</td></tr>
            <tr class="grand-row"><td>Total ({{ $invoice->business->currency }})</td><td class="t-value">{{ number_format($invoice->total, 2) }}</td></tr>
            @if($invoice->amount_paid > 0)
            <tr class="paid-row"><td class="t-label">Amount Paid</td><td class="t-value">-{{ number_format($invoice->amount_paid, 2) }}</td></tr>
            <tr class="due-row"><td>Balance Due</td><td class="t-value">{{ number_format($invoice->balanceDue(), 2) }}</td></tr>
            @endif
        </table>
    </div>
    <div class="clearfix"></div>

    @if($invoice->notes || $invoice->terms)
    <div class="footer">
        <table class="footer-table">
            <tr>
                @if($invoice->notes)
                <td style="width:50%;">
                    <h4>Notes</h4>
                    <p>{{ $invoice->notes }}</p>
                </td>
                @endif
                @if($invoice->terms)
                <td style="width:50%; padding-left: {{ $invoice->notes ? '20px' : '0' }};">
                    <h4>Terms &amp; Conditions</h4>
                    <p>{{ $invoice->terms }}</p>
                </td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    <div class="thankyou">
        Thank you for your business — {{ $invoice->business->name }}
    </div>

</div>
</body>
</html>