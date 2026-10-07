<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    public function download(Invoice $invoice): Response
    {
        $invoice->load(['business', 'customer', 'items.product', 'payments']);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

        $filename = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        return $pdf->download($filename);
    }

    public function stream(Invoice $invoice)
    {
        $invoice->load(['business', 'customer', 'items.product', 'payments']);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

        $filename = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        return $pdf->stream($filename);
    }

    public function render(Invoice $invoice): string
    {
        $invoice->load(['business', 'customer', 'items.product', 'payments']);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
