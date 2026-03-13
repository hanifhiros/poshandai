<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\ApPayment;
use App\Models\ChartOfAccount;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountPayableController extends Controller
{
    private function resolveStore(): array
    {
        $storeId = session('selected_store');
        if (!$storeId) {
            abort(403, 'Pilih store terlebih dahulu.');
        }
        return [$storeId, Store::findOrFail($storeId)];
    }

    public function index(Request $request)
    {
        $request->validate([
            'status'   => 'nullable|in:unpaid,partially_paid,paid',
            'supplier' => 'nullable|integer|exists:suppliers,id',
            'search'   => 'nullable|string|max:200',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $query = AccountPayable::forStore($storeId)->with('supplier', 'payments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }
        if ($request->filled('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('description', 'like', '%' . $escaped . '%');
        }

        $payables = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        // Summary
        $summary = AccountPayable::forStore($storeId)
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(total_amount - paid_amount), 0) as total_outstanding
            ")->first();

        $overdueCount = AccountPayable::forStore($storeId)->outstanding()
            ->where('due_date', '<', now()->toDateString())->count();

        return view('handai-manager.finance.accounts-payable.index', compact(
            'store', 'payables', 'suppliers', 'summary', 'overdueCount'
        ));
    }

    public function create()
    {
        [$storeId, $store] = $this->resolveStore();
        $suppliers = Supplier::orderBy('name')->get();

        return view('handai-manager.finance.accounts-payable.create', compact('store', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'  => 'required|exists:suppliers,id',
            'description'  => 'required|string|max:500',
            'total_amount' => 'required|numeric|min:1',
            'due_date'     => 'required|date',
        ]);

        [$storeId] = $this->resolveStore();

        AccountPayable::create([
            'store_id'     => $storeId,
            'supplier_id'  => $request->supplier_id,
            'description'  => $request->description,
            'total_amount' => $request->total_amount,
            'due_date'     => $request->due_date,
            'status'       => AccountPayable::STATUS_UNPAID,
            'created_by'   => Auth::id(),
        ]);

        return redirect()->route('manager.finance.ap.index')
            ->with('success', 'Hutang berhasil dicatat.');
    }

    public function pay(Request $request, $id)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,e-wallet',
            'notes'          => 'nullable|string|max:500',
        ]);

        [$storeId] = $this->resolveStore();

        $ap = AccountPayable::where('store_id', $storeId)->findOrFail($id);

        $maxPayable = (float) $ap->total_amount - (float) $ap->paid_amount;
        if ((float) $request->amount > $maxPayable) {
            return back()->withErrors(['amount' => "Jumlah pembayaran melebihi sisa hutang (Rp" . number_format($maxPayable, 0, ',', '.') . ")"]);
        }

        DB::transaction(function () use ($request, $storeId, $ap) {
            $paymentSubType = $request->payment_method === 'cash'
                ? ChartOfAccount::SUB_KAS
                : ChartOfAccount::SUB_BANK;

            $journal = AccountingService::createJournal(
                $storeId,
                "Pembayaran hutang: {$ap->description}",
                'MANUAL',
                [
                    [
                        'account_sub_type' => ChartOfAccount::SUB_HUTANG,
                        'debit'  => (float) $request->amount,
                        'credit' => 0,
                        'memo'   => 'Pelunasan hutang usaha',
                    ],
                    [
                        'account_sub_type' => $paymentSubType,
                        'debit'  => 0,
                        'credit' => (float) $request->amount,
                        'memo'   => 'Kas keluar untuk pelunasan',
                    ],
                ]
            );

            ApPayment::create([
                'accounts_payable_id' => $ap->id,
                'amount'              => $request->amount,
                'payment_date'        => $request->payment_date,
                'payment_method'      => $request->payment_method,
                'notes'               => $request->notes,
                'journal_id'          => $journal->id,
                'created_by'          => Auth::id(),
            ]);

            $ap->recalculateStatus();
        });

        return redirect()->route('manager.finance.ap.index')
            ->with('success', 'Pembayaran hutang berhasil dicatat.');
    }
}
