<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request)
    {
        $business = auth()->user()->currentBusiness();
        $from = $request->from;
        $to   = $request->to;
        $type = $request->type ?? 'revenue';

        $data = match ($type) {
            'expenses'    => $this->reportService->expenseReport($business, $from, $to),
            'profit'      => $this->reportService->profitOverview($business, $from, $to),
            'outstanding' => ['invoices' => $this->reportService->outstandingInvoices($business), 'total' => 0],
            'customers'   => ['customers' => $this->reportService->customerRevenue($business, $from, $to), 'total' => 0],
            default       => $this->reportService->revenueReport($business, $from, $to),
        };

        return view('reports.index', compact('data', 'type', 'from', 'to', 'business'));
    }
}
