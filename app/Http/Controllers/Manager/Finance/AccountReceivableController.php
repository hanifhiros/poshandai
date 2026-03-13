<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\AccountReceivable;
use App\Models\ArPayment;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Store;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountReceivableController extends Controller
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
            'customer' => 'nullable|integer|exists:customer,id',
            'search'   => 'nullable|string|max:200',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $query = AccountReceivable::forStore($storeId)->with('customer', 'payments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }
        if ($request->filled('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('description', 'like', '%' . $escaped . '%');
        }

        $receivables = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $customers = Customer::orderBy('name')->get();

        $summary = AccountReceivable::forStore($storeId)
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(total_amount - paid_amount), 0) as total_outstanding
            ")->first();

        $overdueCount = AccountReceivable::forStore($storeId)->outstanding()
            ->where('due_date', '<', now()->toDateString())->count();

        return view('handai-manager.finance.accounts-receivable.index', compact(
            'store', 'receivables', 'customers', 'summary', 'overdueCount'
        ));
    }

    public function create()
    {
        [$storeId, $store] = $this->resolveStore();
        $customers = Customer::orderBy('name')->get();

        return view('handai-manager.finance.accounts-receivable.create', compact('store', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'nullable|exists:customer,id',
            'invoice_number' => 'nullable|string|max:100',
            'description'    => 'required|string|max:500',
            'total_amount'   => 'required|numeric|min:1',
            'due_date'       => 'required|date',
        ]);

        [$storeId] = $this->resolveStore();

        AccountReceivable::create([
            'store_id'       => $storeId,
            'customer_id'    => $request->customer_id,
            'invoice_number' => $request->invoice_number,
            'description'    => $request->description,
            'total_amount'   => $request->total_amount,
            'due_date'       => $request->due_date,
            'status'         => AccountReceivable::STATUS_UNPAID,
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('manager.finance.ar.index')
            ->with('success', 'Piutang berhasil dicatat.');
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

        $ar = AccountReceivable::where('store_id', $storeId)->findOrFail($id);

        $maxPayable = (float) $ar->total_amount - (float) $ar->paid_amount;
        if ((float) $request->amount > $maxPayable) {
            return back()->withErrors(['amount' => "Jumlah melebihi sisa piutang (Rp" . number_format($maxPayable, 0, ',', '.') . ")"]);
        }

        DB::transaction(function () use ($request, $storeId, $ar) {
            $paymentSubType = $request->payment_method === 'cash'
                ? ChartOfAccount::SUB_KAS
                : ChartOfAccount::SUB_BANK;

            $journal = AccountingService::createJournal(
                $storeId,
                "Penerimaan piutang: {$ar->description}",
                'MANUAL',
                [
                    [
                        'account_sub_type' => $paymentSubType,
                        'debit'  => (float) $request->amount,
                        'credit' => 0,
                        'memo'   => 'Penerimaan kas dari piutang',
                    ],
                    [
                        'account_sub_type' => ChartOfAccount::SUB_PIUTANG,
                        'debit'  => 0,
                        'credit' => (float) $request->amount,
                        'memo'   => 'Pelunasan piutang usaha',
                    ],
                ]
            );

            ArPayment::create([
                'accounts_receivable_id' => $ar->id,
                'amount'                 => $request->amount,
                'payment_date'           => $request->payment_date,
                'payment_method'         => $request->payment_method,
                'notes'                  => $request->notes,
                'journal_id'             => $journal->id,
                'created_by'             => Auth::id(),
            ]);

            $ar->recalculateStatus();
        });

        return redirect()->route('manager.finance.ar.index')
            ->with('success', 'Pembayaran piutang berhasil dicatat.');
    }
}
