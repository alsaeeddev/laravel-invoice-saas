<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\DashboardStatsService;

class DashboardController extends Controller
{
    public function __construct(private DashboardStatsService $statsService) {}

    public function index()
    {
        $business = auth()->user()->currentBusiness();
        $stats = $this->statsService->getStats($business);

        return view('dashboard', compact('stats', 'business'));
    }
}
