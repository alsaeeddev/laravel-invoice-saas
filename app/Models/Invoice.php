<?php

namespace App\Models;

use App\Jobs\SendInvoiceEmailJob;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'business_id', 'customer_id', 'invoice_number', 'invoice_date',
        'due_date', 'status', 'subtotal', 'tax_total', 'discount_total',
        'total', 'amount_paid', 'notes', 'terms',
        'sent_at', 'email_status', 'email_type', 'email_error', 'pending_email_token',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Recalculate net amount_paid (payments - refunds) and update status accordingly.
     */
    public function recalculatePaymentStatus(): void
    {
        $totalPayments = $this->payments()->where('type', 'payment')->sum('amount');
        $totalRefunds = $this->payments()->where('type', 'refund')->sum('amount');
        $netPaid = $totalPayments - $totalRefunds;

        $this->amount_paid = max(0, $netPaid);

        if ($totalRefunds > 0 && $netPaid <= 0) {
            $this->status = 'refunded';
        } elseif ($totalRefunds > 0 && $netPaid > 0 && $netPaid < $this->total) {
            $this->status = 'partially_refunded';
        } elseif ($netPaid <= 0) {
            if (in_array($this->status, ['partially_paid', 'paid'])) {
                $this->status = 'sent';
            }
        } elseif ($netPaid >= $this->total) {
            $this->status = 'paid';
        } else {
            $this->status = 'partially_paid';
        }

        $this->saveQuietly();
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'primary',
            'partially_paid' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark',
            'partially_refunded' => 'info',
            'refunded' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Client-facing label
     */
    public function clientStatusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Paid',
            'partially_paid' => 'Partially Paid',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially Refunded',
            'cancelled' => 'Cancelled',
            'draft' => 'Draft',
            default => $this->due_date && $this->due_date->lt(today())
                ? 'Overdue'
                : 'Unpaid',
        };
    }

    /**
     * Admin side email badge color.
     */
    public function emailStatusColor(): string
    {
        return match ($this->email_status) {
            'queued' => 'warning',
            'sent' => 'success',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    public function emailStatusLabel(): string
    {
        return match ($this->email_status) {
            'queued' => 'Queued',
            'sent' => 'Sent',
            'failed' => 'Failed',
            default => 'Not sent',
        };
    }

    /**
     * Determines the email scenario to use after an action. / Note: always call this AFTER recalculatePaymentStatus().
     */
    public function emailScenario(string $action): string
    {
        return match (true) {
            $action === 'cancel' => 'cancelled',
            $action === 'refund' => $this->status === 'refunded' ? 'refunded' : 'partially_refunded',
            $action === 'payment' => $this->balanceDue() <= 0 ? 'paid' : 'partially_paid',
            default => 'invoice',
        };
    }

    /**
     * Puts the email on the queue. When a new email is queued, any earlier pending emails (not yet sent by the worker) are skipped automatically.
     * Returns false if the customer has no email address.
     */
    public function queueEmail(string $action, ?float $amount = null): bool
    {
        if (! $this->customer?->email) {
            return false;
        }

        $token = (string) Str::uuid();
        $scenario = $this->emailScenario($action);

        $this->update([
            'email_status' => 'queued',
            'email_type' => $scenario,
            'email_error' => null,
            'pending_email_token' => $token,
        ]);

        SendInvoiceEmailJob::dispatch($this, $scenario, $amount, $token);

        return true;
    }

    /**
     * For Resend/Retry: returns [action, amount] based on the current status.
     */
    public function resendPayload(): array
    {
        $last = fn (string $type) => (float) ($this->payments()
            ->where('type', $type)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->value('amount') ?? 0);

        return match ($this->status) {
            'paid' => ['payment', $last('payment')],
            'refunded', 'partially_refunded' => ['refund', $last('refund')],
            'cancelled' => ['cancel', null],
            default => ['invoice', null],
        };
    }

    /**
     * Label for the type of the last email (will be null for older rows).
     */
    public function emailTypeLabel(): ?string
    {
        return match ($this->email_type) {
            'invoice' => 'Invoice',
            'paid' => 'Payment Received (Full)',
            'partially_paid' => 'Partial Payment',
            'refunded' => 'Full Refund',
            'partially_refunded' => 'Partial Refund',
            'cancelled' => 'Cancellation Notice',
            default => null,
        };
    }
}
