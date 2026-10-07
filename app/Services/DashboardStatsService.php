<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    // Statuses where some payment (net of refunds) has been received
    private const PAID_STATUSES = ['paid', 'partially_paid', 'partially_refunded', 'refunded'];

    public function getStats(Business $business): array
    {
        $businessId = $business->id;
        $driver     = DB::getDriverName();

        $totalRevenue = DB::table('invoices')
            ->where('business_id', $businessId)
            ->whereIn('status', self::PAID_STATUSES)
            ->sum('amount_paid');

        $paidCount = DB::table('invoices')
            ->where('business_id', $businessId)
            ->whereIn('status', ['paid', 'partially_paid', 'partially_refunded'])
            ->count();

        $unpaidCount = DB::table('invoices')
            ->where('business_id', $businessId)
            ->whereIn('status', ['draft', 'sent'])
            ->count();

        $overdueCount = DB::table('invoices')
            ->where('business_id', $businessId)
            ->where('status', 'overdue')
            ->count();

        $totalCustomers = DB::table('customers')
            ->where('business_id', $businessId)
            ->whereNull('deleted_at')
            ->count();

        $totalExpenses = DB::table('expenses')
            ->where('business_id', $businessId)
            ->sum('amount');

        $recentInvoices = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.business_id', $businessId)
            ->select('invoices.*', 'customers.name as customer_name')
            ->orderByDesc('invoices.created_at')
            ->limit(5)
            ->get();

        $recentPayments = DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('payments.business_id', $businessId)
            ->select('payments.*', 'invoices.invoice_number', 'customers.name as customer_name')
            ->orderByDesc('payments.created_at')
            ->limit(5)
            ->get();

        // Monthly revenue — DB-agnostic date format
        $monthlyRevenue = $this->getMonthlyRevenue($businessId, $driver);

        // Fill in missing months with 0 for the last 12 months
        $months   = [];
        $revenues = [];
        for ($i = 11; $i >= 0; $i--) {
            $month      = now()->subMonths($i)->format('Y-m');
            $months[]   = now()->subMonths($i)->format('M Y');
            $revenues[] = (float) ($monthlyRevenue[$month] ?? 0);
        }

        return compact(
            'totalRevenue', 'paidCount', 'unpaidCount', 'overdueCount',
            'totalCustomers', 'totalExpenses', 'recentInvoices', 'recentPayments',
            'months', 'revenues'
        );
    }

    private function getMonthlyRevenue(int $businessId, string $driver): array
    {
        $since = now()->subYear()->startOfMonth()->toDateString();
        $statusList = "'" . implode("','", self::PAID_STATUSES) . "'";

        if ($driver === 'sqlite') {
            $rows = DB::table('invoices')
                ->where('business_id', $businessId)
                ->whereIn('status', self::PAID_STATUSES)
                ->where('invoice_date', '>=', $since)
                ->selectRaw("strftime('%Y-%m', invoice_date) as month, SUM(amount_paid) as revenue")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('revenue', 'month')
                ->toArray();
        } else {
            $rows = DB::table('invoices')
                ->where('business_id', $businessId)
                ->whereIn('status', self::PAID_STATUSES)
                ->where('invoice_date', '>=', $since)
                ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as month, SUM(amount_paid) as revenue")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('revenue', 'month')
                ->toArray();
        }

        return $rows;
    }
}