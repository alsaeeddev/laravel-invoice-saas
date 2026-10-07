<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $calc = app(InvoiceCalculationService::class);

        // ── Owner user ──────────────────────────────────────────────
        $owner = User::create([
            'name'     => 'Alex Johnson',
            'email'    => 'owner@demo.com',
            'password' => Hash::make('password'),
        ]);

        // ── Staff user ───────────────────────────────────────────────
        $staff = User::create([
            'name'     => 'Sam Lee',
            'email'    => 'staff@demo.com',
            'password' => Hash::make('password'),
        ]);

        // ── Business ─────────────────────────────────────────────────
        $business = Business::create([
            'name'             => 'Acme Creative Studio',
            'email'            => 'hello@acmecreative.com',
            'phone'            => '+1 (555) 100-2000',
            'address'          => '123 Design Ave',
            'city'             => 'San Francisco',
            'country'          => 'USA',
            'currency'         => 'USD',
            'invoice_prefix'   => 'INV-',
            'default_tax_rate' => 10,
            'invoice_notes'    => 'Thank you for your business!',
            'invoice_terms'    => 'Payment due within 30 days.',
        ]);

        $owner->businesses()->attach($business->id, ['role' => 'owner', 'status' => 'active']);
        $staff->businesses()->attach($business->id, ['role' => 'staff', 'status' => 'active']);

        session(['current_business_id' => $business->id]);

        // ── Expense Categories ────────────────────────────────────────
        $catOffice = ExpenseCategory::create(['business_id' => $business->id, 'name' => 'Office Supplies']);
        $catSoftware = ExpenseCategory::create(['business_id' => $business->id, 'name' => 'Software & Tools']);
        $catTravel = ExpenseCategory::create(['business_id' => $business->id, 'name' => 'Travel']);
        $catMarketing = ExpenseCategory::create(['business_id' => $business->id, 'name' => 'Marketing']);

        // ── Products ─────────────────────────────────────────────────
        $products = collect([
            Product::create(['business_id' => $business->id, 'name' => 'Web Design', 'type' => 'service', 'price' => 1500, 'tax_rate' => 10, 'status' => 'active']),
            Product::create(['business_id' => $business->id, 'name' => 'SEO Package', 'type' => 'service', 'price' => 800, 'tax_rate' => 10, 'status' => 'active']),
            Product::create(['business_id' => $business->id, 'name' => 'Logo Design', 'type' => 'service', 'price' => 400, 'tax_rate' => 10, 'status' => 'active']),
            Product::create(['business_id' => $business->id, 'name' => 'Hosting (Annual)', 'type' => 'service', 'price' => 120, 'tax_rate' => 0, 'status' => 'active']),
            Product::create(['business_id' => $business->id, 'name' => 'WordPress Plugin', 'type' => 'product', 'price' => 79, 'tax_rate' => 10, 'status' => 'active']),
        ]);

        // ── Customers ────────────────────────────────────────────────
        $customers = collect([
            Customer::create(['business_id' => $business->id, 'name' => 'Maria Torres', 'company' => 'Torres Consulting', 'email' => 'maria@torresco.com', 'phone' => '+1-555-0101', 'city' => 'New York', 'country' => 'USA']),
            Customer::create(['business_id' => $business->id, 'name' => 'James Chen', 'company' => 'Chen Tech', 'email' => 'james@chentech.io', 'phone' => '+1-555-0202', 'city' => 'Austin', 'country' => 'USA']),
            Customer::create(['business_id' => $business->id, 'name' => 'Sophie Martin', 'company' => 'Martin Digital', 'email' => 'sophie@martindigital.fr', 'phone' => '+33-1-2345', 'city' => 'Paris', 'country' => 'France']),
            Customer::create(['business_id' => $business->id, 'name' => 'Omar Hassan', 'company' => null, 'email' => 'omar.hassan@email.com', 'phone' => '+44-20-9876', 'city' => 'London', 'country' => 'UK']),
            Customer::create(['business_id' => $business->id, 'name' => 'Priya Patel', 'company' => 'Patel Ventures', 'email' => 'priya@patelventures.in', 'phone' => '+91-98765', 'city' => 'Mumbai', 'country' => 'India']),
        ]);

        // ── Helper to create an invoice ───────────────────────────────
        $makeInvoice = function(Customer $customer, array $itemData, string $status, int $daysAgo, int $dueDays) use ($business, $calc) {
            $result = $calc->calculate($itemData);
            $invoice = Invoice::create([
                'business_id'    => $business->id,
                'customer_id'    => $customer->id,
                'invoice_number' => $business->reserveNextInvoiceNumber(),
                'invoice_date'   => now()->subDays($daysAgo)->toDateString(),
                'due_date'       => now()->subDays($daysAgo)->addDays($dueDays)->toDateString(),
                'status'         => $status,
                'subtotal'       => $result['subtotal'],
                'tax_total'      => $result['taxTotal'],
                'discount_total' => $result['discountTotal'],
                'total'          => $result['total'],
                'amount_paid'    => 0,
                'notes'          => 'Thank you for your business!',
                'terms'          => 'Payment due within 30 days.',
            ]);
            foreach ($result['processedItems'] as $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
            }
            return $invoice;
        };

        // ── Invoices (12 across all statuses) ─────────────────────────
        // Paid invoices
        $inv1 = $makeInvoice($customers[0], [
            ['description'=>'Web Design - Homepage','quantity'=>1,'unit_price'=>1500,'tax_rate'=>10,'discount_amount'=>0,'product_id'=>$products[0]->id],
            ['description'=>'SEO Setup','quantity'=>1,'unit_price'=>800,'tax_rate'=>10,'discount_amount'=>50,'product_id'=>$products[1]->id],
        ], 'paid', 60, 30);
        Payment::create(['invoice_id'=>$inv1->id,'business_id'=>$business->id,'amount'=>$inv1->total,'payment_date'=>now()->subDays(30)->toDateString(),'method'=>'bank_transfer']);
        $inv1->update(['amount_paid'=>$inv1->total,'status'=>'paid']);

        $inv2 = $makeInvoice($customers[1], [
            ['description'=>'Logo Design','quantity'=>1,'unit_price'=>400,'tax_rate'=>10,'discount_amount'=>0,'product_id'=>$products[2]->id],
            ['description'=>'WordPress Plugin License','quantity'=>3,'unit_price'=>79,'tax_rate'=>10,'discount_amount'=>0,'product_id'=>$products[4]->id],
        ], 'paid', 45, 30);
        Payment::create(['invoice_id'=>$inv2->id,'business_id'=>$business->id,'amount'=>$inv2->total,'payment_date'=>now()->subDays(15)->toDateString(),'method'=>'card']);
        $inv2->update(['amount_paid'=>$inv2->total,'status'=>'paid']);

        $inv3 = $makeInvoice($customers[2], [
            ['description'=>'Annual Hosting','quantity'=>1,'unit_price'=>120,'tax_rate'=>0,'discount_amount'=>0,'product_id'=>$products[3]->id],
            ['description'=>'Maintenance (3 months)','quantity'=>3,'unit_price'=>200,'tax_rate'=>10,'discount_amount'=>0],
        ], 'paid', 90, 30);
        Payment::create(['invoice_id'=>$inv3->id,'business_id'=>$business->id,'amount'=>$inv3->total,'payment_date'=>now()->subDays(60)->toDateString(),'method'=>'cash']);
        $inv3->update(['amount_paid'=>$inv3->total,'status'=>'paid']);

        // Partially paid
        $inv4 = $makeInvoice($customers[0], [
            ['description'=>'E-commerce Website Development','quantity'=>1,'unit_price'=>3500,'tax_rate'=>10,'discount_amount'=>0],
        ], 'partially_paid', 20, 30);
        Payment::create(['invoice_id'=>$inv4->id,'business_id'=>$business->id,'amount'=>1500,'payment_date'=>now()->subDays(10)->toDateString(),'method'=>'bank_transfer','reference_number'=>'TXN-001']);
        $inv4->update(['amount_paid'=>1500,'status'=>'partially_paid']);

        // Sent (pending)
        $inv5 = $makeInvoice($customers[3], [
            ['description'=>'Brand Identity Package','quantity'=>1,'unit_price'=>1200,'tax_rate'=>10,'discount_amount'=>100],
        ], 'sent', 5, 25);

        $inv6 = $makeInvoice($customers[4], [
            ['description'=>'SEO Monthly Retainer','quantity'=>3,'unit_price'=>400,'tax_rate'=>10,'discount_amount'=>0,'product_id'=>$products[1]->id],
        ], 'sent', 3, 30);

        // Overdue
        $inv7 = $makeInvoice($customers[1], [
            ['description'=>'Mobile App UI Design','quantity'=>1,'unit_price'=>2200,'tax_rate'=>10,'discount_amount'=>0],
        ], 'overdue', 45, 15);

        $inv8 = $makeInvoice($customers[2], [
            ['description'=>'Content Writing (10 articles)','quantity'=>10,'unit_price'=>80,'tax_rate'=>10,'discount_amount'=>0],
        ], 'overdue', 60, 14);

        // Draft
        $inv9 = $makeInvoice($customers[4], [
            ['description'=>'Social Media Strategy','quantity'=>1,'unit_price'=>650,'tax_rate'=>10,'discount_amount'=>0],
        ], 'draft', 1, 30);

        // Cancelled
        $inv10 = $makeInvoice($customers[3], [
            ['description'=>'Video Production','quantity'=>1,'unit_price'=>3000,'tax_rate'=>10,'discount_amount'=>0],
        ], 'cancelled', 30, 30);

        // More paid for chart variety
        $inv11 = $makeInvoice($customers[4], [
            ['description'=>'Website Redesign','quantity'=>1,'unit_price'=>2500,'tax_rate'=>10,'discount_amount'=>200],
        ], 'paid', 120, 30);
        Payment::create(['invoice_id'=>$inv11->id,'business_id'=>$business->id,'amount'=>$inv11->total,'payment_date'=>now()->subDays(90)->toDateString(),'method'=>'card']);
        $inv11->update(['amount_paid'=>$inv11->total,'status'=>'paid']);

        $inv12 = $makeInvoice($customers[2], [
            ['description'=>'Consulting (5 hours)','quantity'=>5,'unit_price'=>150,'tax_rate'=>10,'discount_amount'=>0],
        ], 'paid', 150, 30);
        Payment::create(['invoice_id'=>$inv12->id,'business_id'=>$business->id,'amount'=>$inv12->total,'payment_date'=>now()->subDays(120)->toDateString(),'method'=>'bank_transfer']);
        $inv12->update(['amount_paid'=>$inv12->total,'status'=>'paid']);

        // ── Expenses ─────────────────────────────────────────────────
        Expense::create(['business_id'=>$business->id,'category_id'=>$catSoftware->id,'title'=>'Adobe Creative Cloud','amount'=>599,'expense_date'=>now()->subDays(30)->toDateString(),'payment_method'=>'Card']);
        Expense::create(['business_id'=>$business->id,'category_id'=>$catOffice->id,'title'=>'Office Desk & Chair','amount'=>850,'expense_date'=>now()->subDays(45)->toDateString(),'payment_method'=>'Bank Transfer']);
        Expense::create(['business_id'=>$business->id,'category_id'=>$catTravel->id,'title'=>'Client Meeting - NYC','amount'=>320,'expense_date'=>now()->subDays(20)->toDateString(),'payment_method'=>'Cash']);
        Expense::create(['business_id'=>$business->id,'category_id'=>$catMarketing->id,'title'=>'LinkedIn Ads - Q3','amount'=>450,'expense_date'=>now()->subDays(10)->toDateString(),'payment_method'=>'Card']);
        Expense::create(['business_id'=>$business->id,'category_id'=>$catSoftware->id,'title'=>'Slack Premium','amount'=>96,'expense_date'=>now()->subDays(60)->toDateString(),'payment_method'=>'Card']);

        $this->command->info('✅ Demo seeded! Login: owner@demo.com / password');
    }
}
