<?php

namespace Tests\Feature;

use App\Services\InvoiceCalculationService;
use Tests\TestCase;

class InvoiceCalculationTest extends TestCase
{
    public function test_calculation_service_computes_correct_totals(): void
    {
        $calc = new InvoiceCalculationService();
        $result = $calc->calculate([
            ['quantity' => 2, 'unit_price' => 100, 'tax_rate' => 10, 'discount_amount' => 20],
            ['quantity' => 1, 'unit_price' => 50,  'tax_rate' => 0,  'discount_amount' => 0],
        ]);

        // Line 1: gross=200, disc=20, taxable=180, tax=18, total=198
        // Line 2: gross=50,  disc=0,  taxable=50,  tax=0,  total=50
        $this->assertEquals(230.00, $result['subtotal']);
        $this->assertEquals(18.00,  $result['taxTotal']);
        $this->assertEquals(20.00,  $result['discountTotal']);
        $this->assertEquals(248.00, $result['total']);
    }

    public function test_discount_capped_at_line_gross(): void
    {
        $calc = new InvoiceCalculationService();
        $result = $calc->calculate([
            ['quantity' => 1, 'unit_price' => 100, 'tax_rate' => 10, 'discount_amount' => 200],
        ]);
        // Discount capped at 100, taxable=0, tax=0, total=0
        $this->assertEquals(0.00, $result['total']);
        $this->assertEquals(100.00, $result['discountTotal']);
    }

    public function test_zero_tax_rate_produces_no_tax(): void
    {
        $calc = new InvoiceCalculationService();
        $result = $calc->calculate([
            ['quantity' => 5, 'unit_price' => 20, 'tax_rate' => 0, 'discount_amount' => 0],
        ]);
        $this->assertEquals(100.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['taxTotal']);
        $this->assertEquals(100.00, $result['total']);
    }
}
