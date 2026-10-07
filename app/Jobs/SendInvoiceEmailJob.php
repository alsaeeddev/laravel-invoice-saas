<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300]; // retry: 30s, 2min, 5min
    public int $timeout = 60;

    // If the invoice has been deleted, silently discard the job
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public Invoice $invoice,
        public string $scenario = 'invoice',
        public ?float $amount = null,
        public ?string $token = null,
    ) {}

    public function handle(): void
    {
        // Load the latest state from the DB (the worker has no auth user, so the tenant scope is not applied)
        $invoice = $this->invoice->fresh(['customer', 'business', 'items', 'payments']);

        if (! $invoice) {
            return;
        }

        // A newer email has been queued after this job -> skip this one
        if ($this->isSuperseded($invoice)) {
            return;
        }

        $email = $invoice->customer?->email;

        if (! $email) {
            $invoice->update([
                'email_status'        => 'failed',
                'email_error'         => 'Customer has no email address.',
                'pending_email_token' => null,
            ]);
            return;
        }

        Mail::to($email)->send(new InvoiceMail($invoice, $this->scenario, $this->amount));

        $invoice->update([
            'email_status'        => 'sent',
            'sent_at'             => now(),
            'email_error'         => null,
            'pending_email_token' => null,
        ]);
    }

    // Runs after all retries have failed
    public function failed(\Throwable $e): void
    {
        $invoice = $this->invoice->fresh();

        // If a stale (older) job fails, don't overwrite the status of the latest email
        if (! $invoice || $this->isSuperseded($invoice)) {
            return;
        }

        $invoice->update([
            'email_status' => 'failed',
            'email_error'  => mb_substr($e->getMessage(), 0, 500),
        ]);
    }

    private function isSuperseded(Invoice $invoice): bool
    {
        return $this->token !== null
            && $invoice->pending_email_token !== $this->token;
    }
}