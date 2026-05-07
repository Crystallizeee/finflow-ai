<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $report = $this->reportService->generateMonthlyReport($request->user());

        return Inertia::render('Finance/Reports', [
            'report' => $report
        ]);
    }
}
