<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreRefundRequest;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Invoice $invoice)
    {
        $payments = $invoice->payments()->latest()->get();
        return view('payments.index', compact('invoice', 'payments'));
    }

    public function create(Invoice $invoice)
    {
        $invoice->loadMissing('customer');
        return view('payments.create', compact('invoice'));
    }

    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        $balanceDue = $invoice->balanceDue();
        if ($request->amount > $balanceDue + 0.001) {
            throw ValidationException::withMessages([
                'amount' => "Payment amount ({$request->amount}) exceeds balance due ({$balanceDue}).",
            ]);
        }

        Payment::create([
            'invoice_id'       => $invoice->id,
            'business_id'      => $invoice->business_id,
            'type'             => 'payment',
            'amount'           => $request->amount,
            'payment_date'     => $request->payment_date,
            'method'           => $request->method,
            'reference_number' => $request->reference_number,
            'notes'            => $request->notes,
        ]);

        $invoice->recalculatePaymentStatus();

        $msg = 'Payment recorded successfully.' . $this->emailNote($request, $invoice, 'payment', (float) $request->amount);

        return redirect()->route('invoices.show', $invoice)->with('success', $msg);
    }

    public function destroy(Invoice $invoice, Payment $payment)
    {
        if ($payment->invoice_id !== $invoice->id) {
            abort(404);
        }

        $payment->delete();
        $invoice->recalculatePaymentStatus();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment removed.');
    }

    public function createRefund(Invoice $invoice)
    {
        if ($invoice->amount_paid <= 0) {
            return back()->with('error', 'No payments available to refund on this invoice.');
        }

        $invoice->loadMissing('customer');

        return view('invoices.refunds.create', compact('invoice'));
    }

    public function storeRefund(StoreRefundRequest $request, Invoice $invoice)
    {
        Payment::create([
            'invoice_id'       => $invoice->id,
            'business_id'      => $invoice->business_id,
            'type'             => 'refund',
            'amount'           => $request->amount,
            'payment_date'     => $request->payment_date,
            'method'           => $request->method,
            'reference_number' => $request->reference_number,
            'notes'            => $request->notes,
        ]);

        $invoice->recalculatePaymentStatus();

        $msg = 'Refund recorded successfully.' . $this->emailNote($request, $invoice, 'refund', (float) $request->amount);

        return redirect()->route('invoices.show', $invoice)->with('success', $msg);
    }

    /**
     * Queues the email if the checkbox is ticked, and returns a fragment for the flash message.
     */
    private function emailNote(Request $request, Invoice $invoice, string $action, float $amount): string
    {
        if (! $request->boolean('send_email')) {
            return '';
        }

        $invoice->loadMissing('customer');
        $email = $invoice->customer?->email;

        return $invoice->queueEmail($action, $amount)
            ? " Email queued to {$email}."
            : ' (Email not sent: customer has no email address.)';
    }
}