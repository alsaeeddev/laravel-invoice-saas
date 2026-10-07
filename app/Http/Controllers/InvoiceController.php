<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceCalculationService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceCalculationService $calculator,
        private InvoicePdfService $pdfService
    ) {}

    public function index(Request $request)
    {
        $query = Invoice::with('customer')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', '%'.$request->search.'%'));
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $business = auth()->user()->currentBusiness();

        return view('invoices.create', compact('customers', 'products', 'business'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $business = auth()->user()->currentBusiness();
        $calc = $this->calculator->calculate($request->items);

        $invoice = DB::transaction(function () use ($request, $business, $calc) {
            $invoiceNumber = $business->reserveNextInvoiceNumber();

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $request->customer_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'status' => 'draft',
                'subtotal' => $calc['subtotal'],
                'tax_total' => $calc['taxTotal'],
                'discount_total' => $calc['discountTotal'],
                'total' => $calc['total'],
                'amount_paid' => 0,
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            foreach ($calc['processedItems'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['business', 'customer', 'items.product', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $invoice->load('items');
        $business = auth()->user()->currentBusiness();

        return view('invoices.edit', compact('invoice', 'customers', 'products', 'business'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $calc = $this->calculator->calculate($request->items);

        DB::transaction(function () use ($request, $invoice, $calc) {
            $invoice->update([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $calc['subtotal'],
                'tax_total' => $calc['taxTotal'],
                'discount_total' => $calc['discountTotal'],
                'total' => $calc['total'],
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            $invoice->items()->delete();

            foreach ($calc['processedItems'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function pdf(Invoice $invoice)
    {
        return $this->pdfService->download($invoice);
    }

    /**
     * First-time send (drafts only). Resend/Retry is used afterwards.
     */
    public function send(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'This invoice was already sent. Use Resend instead.');
        }

        $invoice->loadMissing('customer');
        $email = $invoice->customer?->email;

        if (! $email) {
            return back()->with('error', 'Customer has no email address. Add one from the customer page first.');
        }

        $invoice->queueEmail('invoice');
        $invoice->update(['status' => 'sent']);

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice queued for {$email}.");
    }

    /**
     * Handles both Resend (sent/partial/paid/refunded/cancelled) and Retry (failed).
     *The email is built based on the current status.
     */
    public function resend(Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            return back()->with('error', 'Draft invoice must be sent first.');
        }

        $invoice->loadMissing('customer');
        $email = $invoice->customer?->email;

        if (! $email) {
            return back()->with('error', 'Customer has no email address. Add one from the customer page first.');
        }

        [$action, $amount] = $invoice->resendPayload();
        $invoice->queueEmail($action, $amount);

        return redirect()->route('invoices.show', $invoice)->with('success', "Email queued to {$email}.");
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['cancelled', 'paid'])) {
            return back()->with('error', 'This invoice cannot be cancelled.');
        }

        $wasDraft = $invoice->status === 'draft';

        $invoice->update(['status' => 'cancelled']);

        $msg = 'Invoice cancelled.';

        // A draft was never sent to the customer, so no cancellation email is sent for it
        if ($request->boolean('send_email') && ! $wasDraft) {
            $invoice->loadMissing('customer');
            $email = $invoice->customer?->email;

            $msg .= $invoice->queueEmail('cancel')
                ? " Email queued to {$email}."
                : ' (Email not sent: customer has no email address.)';
        }

        return redirect()->route('invoices.show', $invoice)->with('success', $msg);
    }

    public function bulkDestroy(Request $request)
    {
        $business = auth()->user()->currentBusiness();

        if (! $business || ! auth()->user()->isOwnerOf($business)) {
            abort(403);
        }

        $ids = $request->input('invoice_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No invoices selected.');
        }

        // The business_id scope is required, otherwise a user could delete another business's invoices
        Invoice::where('business_id', $business->id)
            ->whereIn('id', $ids)
            ->delete();

        return back()->with('success', count($ids).' invoice(s) deleted successfully.');
    }

    public function destroyAll()
    {
        $business = auth()->user()->currentBusiness();

        if (! $business || ! auth()->user()->isOwnerOf($business)) {
            abort(403);
        }

        Invoice::where('business_id', $business->id)->delete();

        return back()->with('success', 'All invoices deleted successfully.');
    }
}
