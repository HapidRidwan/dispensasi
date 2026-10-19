<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensation;
use App\Models\DispensationLog;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index', [
            'total' => Dispensation::count(),
            'byStatus' => Dispensation::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'logs' => DispensationLog::latest('created_at')->limit(50)->get(),
        ]);
    }
}
