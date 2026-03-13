<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Services\OperationalKpiService;
use Illuminate\Http\Request;

class OperationalKpiController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $kpis = OperationalKpiService::getKpis($storeId, $startDate, $endDate);

        return view('handai-manager.operational.kpi-dashboard', compact('kpis', 'startDate', 'endDate'));
    }
}
