<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ExecutiveReportService;

class ExecutiveReportController extends Controller
{
    protected ExecutiveReportService $reportService;

    public function __construct(ExecutiveReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the Executive Strategic Summary Report & Printable Portal
     */
    public function index(Request $request)
    {
        $yearId = $request->filled('year_id') && $request->year_id !== 'all' ? (int) $request->year_id : null;
        $deptId = $request->filled('department_id') && $request->department_id !== 'all' ? (int) $request->department_id : null;

        $reportData = $this->reportService->getReportData($yearId, $deptId);
        $years = $this->reportService->getAvailableYears();
        $faculties = $this->reportService->getFaculties();

        return view('backend.reports.executive_summary', array_merge($reportData, [
            'years' => $years,
            'faculties' => $faculties,
        ]));
    }
}
