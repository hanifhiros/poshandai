<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RNDStockUsage;
class FinanceController extends Controller
{
    public function index()
{
    $pendingRequests = RNDStockUsage::with(['unit', 'stock', 'rndHistory'])
        ->where('status', 'pending')
        ->get();

    return view('handai-manager.finance.rnd-request', compact('pendingRequests'));
}

}
