@php
    $cur         = $invoice->business->currency;
    $refunded    = $invoice->payments->where('type', 'refund')->sum('amount');
    $money       = fn ($n) => $cur . ' ' . number_format((float) $n, 2);
    $showSummary = $scenario !== 'invoice' || $invoice->amount_paid > 0;

    [$color, $title] = match ($scenario) {
        'paid'               => ['#16a34a', 'Payment Received'],
        'partially_paid'     => ['#d97706', 'Partial Payment Received'],
        'refunded'           => ['#0ea5e9', 'Refund Issued'],
        'partially_refunded' => ['#0ea5e9', 'Partial Refund Issued'],
        'cancelled'          => ['#475569', 'Invoice Cancelled'],
        default              => ['#6366f1', 'Invoice ' . $invoice->invoice_number],
    };
@endphp
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{{ $title }} - {{ $invoice->invoice_number }}</title></head>
<body style="font-family:sans-serif;color:#1e293b;max-width:600px;margin:0 auto;padding:20px;">
    <div style="background:{{ $color }};color:white;padding:24px;border-radius:12px 12px 0 0;">
        <h2 style="margin:0;">{{ $title }}</h2>
        <p style="margin:4px 0 0;opacity:0.85;">Invoice {{ $invoice->invoice_number }} · {{ $invoice->business->name }}</p>
    </div>

    <div style="border:1px solid #e2e8f0;border-top:none;padding:24px;border-radius:0 0 12px 12px;">
        <p>Dear <strong>{{ $invoice->customer->name }}</strong>,</p>

        @switch($scenario)
            @case('paid')
                <p>Thank you! We have received your payment of <strong>{{ $money($amount) }}</strong> against invoice <strong>{{ $invoice->invoice_number }}</strong>. Your invoice is now <strong>fully paid</strong>.</p>
                @break

            @case('partially_paid')
                <p>We have received your payment of <strong>{{ $money($amount) }}</strong> against invoice <strong>{{ $invoice->invoice_number }}</strong>. The remaining balance is <strong>{{ $money($invoice->balanceDue()) }}</strong>, due on <strong>{{ $invoice->due_date->format('M d, Y') }}</strong>.</p>
                @break

            @case('refunded')
                <p>We have issued a full refund of <strong>{{ $money($amount) }}</strong> for invoice <strong>{{ $invoice->invoice_number }}</strong>.</p>
                @break

            @case('partially_refunded')
                <p>We have issued a partial refund of <strong>{{ $money($amount) }}</strong> for invoice <strong>{{ $invoice->invoice_number }}</strong>. Net amount paid is now <strong>{{ $money($invoice->amount_paid) }}</strong>.</p>
                @break

            @case('cancelled')
                <p>Invoice <strong>{{ $invoice->invoice_number }}</strong> has been <strong>cancelled</strong>. No further payment is required for it.</p>
                @if($invoice->amount_paid > 0)
                    <p>Our records show a net payment of <strong>{{ $money($invoice->amount_paid) }}</strong> on this invoice. Please contact us regarding it.</p>
                @endif
                @break

            @default
                <p>Please find attached your invoice <strong>{{ $invoice->invoice_number }}</strong> for <strong>{{ $money($invoice->total) }}</strong>, due on <strong>{{ $invoice->due_date->format('M d, Y') }}</strong>.</p>
        @endswitch

        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
            <tr style="background:#f8fafc;">
                <th style="padding:8px 12px;text-align:left;font-size:12px;color:#64748b;">Description</th>
                <th style="padding:8px 12px;text-align:right;font-size:12px;color:#64748b;">Amount</th>
            </tr>
            @foreach($invoice->items as $item)
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px 12px;font-size:13px;">{{ $item->description }}</td>
                <td style="padding:8px 12px;text-align:right;font-size:13px;">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td style="padding:12px;text-align:right;font-weight:bold;">Total:</td>
                <td style="padding:12px;text-align:right;font-size:16px;font-weight:bold;color:{{ $color }};">{{ $money($invoice->total) }}</td>
            </tr>
            @if($showSummary)
                <tr>
                    <td style="padding:4px 12px;text-align:right;font-size:13px;color:#64748b;">Net Paid:</td>
                    <td style="padding:4px 12px;text-align:right;font-size:13px;">{{ $money($invoice->amount_paid) }}</td>
                </tr>
                @if($refunded > 0)
                <tr>
                    <td style="padding:4px 12px;text-align:right;font-size:13px;color:#64748b;">Total Refunded:</td>
                    <td style="padding:4px 12px;text-align:right;font-size:13px;">{{ $money($refunded) }}</td>
                </tr>
                @endif
                @if(! in_array($scenario, ['cancelled', 'refunded']))
                <tr>
                    <td style="padding:4px 12px;text-align:right;font-size:13px;font-weight:bold;">Balance Due:</td>
                    <td style="padding:4px 12px;text-align:right;font-size:13px;font-weight:bold;">{{ $money($invoice->balanceDue()) }}</td>
                </tr>
                @endif
            @endif
        </table>

        @if($scenario === 'invoice')
            @if($invoice->notes)<p style="background:#f8fafc;padding:12px;border-radius:8px;font-size:13px;">{{ $invoice->notes }}</p>@endif
            @if($invoice->terms)<p style="font-size:12px;color:#64748b;">{{ $invoice->terms }}</p>@endif
        @endif

        <p>The {{ $scenario === 'invoice' ? '' : 'updated ' }}PDF invoice is attached to this email.</p>
        <p>Thank you for your business!</p>
        <p>Best regards,<br><strong>{{ $invoice->business->name }}</strong></p>
    </div>
</body>
</html>