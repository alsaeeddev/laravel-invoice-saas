<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function revenueReport(Business $business, ?string $from, ?string $to): array
    {
        $query = DB::table('invoices')
            ->where('invoices.business_id', $business->id)
            ->where('invoices.status', 'paid');

        if ($from) $query->where('invoices.invoice_date', '>=', $from);
        if ($to)   $query->where('invoices.invoice_date', '<=', $to);

        $invoices = $query
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select('invoices.*', 'customers.name as customer_name')
            ->orderByDesc('invoices.invoice_date')
            ->get();

        $total = $invoices->sum('total');

        return compact('invoices', 'total');
    }

    public function expenseReport(Business $business, ?string $from, ?string $to): array
    {
        $query = DB::table('expenses')
            ->where('expenses.business_id', $business->id);

        if ($from) $query->where('expenses.expense_date', '>=', $from);
        if ($to)   $query->where('expenses.expense_date', '<=', $to);

        $expenses = $query
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expenses.*', 'expense_categories.name as category_name')
            ->orderByDesc('expenses.expense_date')
            ->get();

        $total = $expenses->sum('amount');

        return compact('expenses', 'total');
    }

    public function profitOverview(Business $business, ?string $from, ?string $to): array
    {
        ['total' => $revenue] = $this->revenueReport($business, $from, $to);
        ['total' => $expenses] = $this->expenseReport($business, $from, $to);
        $profit = $revenue - $expenses;

        return compact('revenue', 'expenses', 'profit');
    }

    public function outstandingInvoices(Business $business): \Illuminate\Support\Collection
    {
        return DB::table('invoices')
            ->where('invoices.business_id', $business->id)
            ->whereIn('invoices.status', ['sent', 'partially_paid', 'overdue'])
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'invoices.*',
                'customers.name as customer_name',
                DB::raw('(invoices.total - invoices.amount_paid) as balance_due')
            )
            ->orderByDesc('invoices.due_date')
            ->get();
    }

    public function customerRevenue(Business $business, ?string $from, ?string $to): \Illuminate\Support\Collection
    {
        $query = DB::table('invoices')
            ->where('invoices.business_id', $business->id)
            ->where('invoices.status', 'paid');

        if ($from) $query->where('invoices.invoice_date', '>=', $from);
        if ($to)   $query->where('invoices.invoice_date', '<=', $to);

        return $query
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->selectRaw('customers.id, customers.name, customers.company, SUM(invoices.total) as total_revenue, COUNT(invoices.id) as invoice_count')
            ->groupBy('customers.id', 'customers.name', 'customers.company')
            ->orderByDesc('total_revenue')
            ->get();
    }
}
