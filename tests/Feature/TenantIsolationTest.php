<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createBusinessWithUser(string $name = null): array
    {
        $user = User::factory()->create();
        $business = Business::create(['name' => $name ?? 'Test Business ' . rand(1000, 9999)]);
        $user->businesses()->attach($business->id, ['role' => 'owner', 'status' => 'active']);
        return [$user, $business];
    }

    public function test_user_cannot_view_another_business_customer(): void
    {
        [$userA, $businessA] = $this->createBusinessWithUser('Business A');
        [$userB, $businessB] = $this->createBusinessWithUser('Business B');

        $customer = Customer::withoutGlobalScopes()->create([
            'business_id' => $businessA->id,
            'name'        => 'BusinessA Customer',
        ]);

        $this->actingAs($userB)
            ->withSession(['current_business_id' => $businessB->id])
            ->get(route('customers.show', $customer->id))
            ->assertNotFound();
    }

    public function test_user_cannot_view_another_business_invoice(): void
    {
        [$userA, $businessA] = $this->createBusinessWithUser('Business A');
        [$userB, $businessB] = $this->createBusinessWithUser('Business B');

        $customerA = Customer::withoutGlobalScopes()->create(['business_id' => $businessA->id, 'name' => 'CustA']);

        $invoice = Invoice::withoutGlobalScopes()->create([
            'business_id'    => $businessA->id,
            'customer_id'    => $customerA->id,
            'invoice_number' => 'INV-0001',
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(30)->toDateString(),
            'status'         => 'draft',
            'subtotal'       => 100, 'tax_total' => 10,
            'discount_total' => 0, 'total' => 110, 'amount_paid' => 0,
        ]);

        $this->actingAs($userB)
            ->withSession(['current_business_id' => $businessB->id])
            ->get(route('invoices.show', $invoice->id))
            ->assertNotFound();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        [$user, $business] = $this->createBusinessWithUser('My Business');

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('invoices.index'))->assertRedirect(route('login'));
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }
}
