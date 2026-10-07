<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $scenario = 'invoice',
        public ?float $amount = null,
    ) {}

    public function envelope(): Envelope
    {
        $no  = $this->invoice->invoice_number;
        $biz = $this->invoice->business->name;

        $subject = match ($this->scenario) {
            'paid'               => "Payment received – Invoice {$no} is fully paid",
            'partially_paid'     => "Payment received for Invoice {$no} – balance remaining",
            'refunded'           => "Refund issued for Invoice {$no}",
            'partially_refunded' => "Partial refund issued for Invoice {$no}",
            'cancelled'          => "Invoice {$no} has been cancelled",
            default              => "Invoice {$no} from {$biz}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    public function attachments(): array
    {
        $pdfService = app(InvoicePdfService::class);
        $pdfContent = $pdfService->render($this->invoice);
        $filename   = str_replace(['/', '\\'], '-', $this->invoice->invoice_number) . '.pdf';

        return [
            Attachment::fromData(fn () => $pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}