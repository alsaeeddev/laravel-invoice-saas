<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeInvoice(int $total): Invoice
    {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Biz']);
        $user->businesses()->attach($business->id, ['role' => 'owner', 'status' => 'active']);
        $customer = Customer::withoutGlobalScopes()->create(['business_id' => $business->id, 'name' => 'Test']);

        return Invoice::withoutGlobalScopes()->create([
            'business_id'    => $business->id,
            'customer_id'    => $customer->id,
            'invoice_number' => 'INV-TEST',
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(30)->toDateString(),
            'status'         => 'sent',
            'subtotal'       => $total,
            'tax_total'      => 0,
            'discount_total' => 0,
            'total'          => $total,
            'amount_paid'    => 0,
        ]);
    }

    public function test_partial_payment_sets_partially_paid_status(): void
    {
        $invoice = $this->makeInvoice(110);

        $invoice->payments()->create([
            'business_id' => $invoice->business_id,
            'amount' => 50,
            'payment_date' => now()->toDateString(),
            'method' => 'cash',
        ]);
        $invoice->recalculatePaymentStatus();

        $this->assertEquals('partially_paid', $invoice->fresh()->status);
        $this->assertEquals(50, $invoice->fresh()->amount_paid);
    }

    public function test_full_payment_sets_paid_status(): void
    {
        $invoice = $this->makeInvoice(110);

        $invoice->payments()->create([
            'business_id' => $invoice->business_id,
            'amount' => 110,
            'payment_date' => now()->toDateString(),
            'method' => 'card',
        ]);
        $invoice->recalculatePaymentStatus();

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_two_partial_payments_reaching_total_becomes_paid(): void
    {
        $invoice = $this->makeInvoice(110);

        foreach ([50, 60] as $amount) {
            $invoice->payments()->create([
                'business_id' => $invoice->business_id,
                'amount'      => $amount,
                'payment_date' => now()->toDateString(),
                'method'      => 'cash',
            ]);
        }
        $invoice->recalculatePaymentStatus();

        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertEquals(110, $invoice->fresh()->amount_paid);
    }
}
