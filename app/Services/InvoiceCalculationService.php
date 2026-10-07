<?php

namespace App\Services;

class InvoiceCalculationService
{
    /**
     * Calculate invoice totals from an array of items.
     *
     * Each item should have: quantity, unit_price, tax_rate, discount_amount
     *
     * @param  array<int, array{quantity: float|string, unit_price: float|string, tax_rate: float|string, discount_amount: float|string}>  $items
     * @return array{subtotal: float, tax_total: float, discount_total: float, total: float, items: array}
     */
    public function calculate(array $items): array
    {
        $subtotal      = 0.0;
        $taxTotal      = 0.0;
        $discountTotal = 0.0;
        $processedItems = [];

        foreach ($items as $item) {
            $qty      = (float) ($item['quantity']       ?? 1);
            $price    = (float) ($item['unit_price']     ?? 0);
            $taxRate  = (float) ($item['tax_rate']       ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);

            // Line subtotal before tax, after discount
            $lineGross    = $qty * $price;
            $lineDiscount = min($discount, $lineGross); // cap discount at gross
            $lineTaxable  = $lineGross - $lineDiscount;
            $lineTax      = round($lineTaxable * ($taxRate / 100), 2);
            $lineTotal    = round($lineTaxable + $lineTax, 2);

            $subtotal      += round($lineGross - $lineDiscount, 2);
            $taxTotal      += $lineTax;
            $discountTotal += $lineDiscount;

            $processedItems[] = array_merge($item, [
                'line_total' => $lineTotal,
            ]);
        }

        $subtotal      = round($subtotal, 2);
        $taxTotal      = round($taxTotal, 2);
        $discountTotal = round($discountTotal, 2);
        $total         = round($subtotal + $taxTotal, 2);

        return compact('subtotal', 'taxTotal', 'discountTotal', 'total', 'processedItems');
    }
}
