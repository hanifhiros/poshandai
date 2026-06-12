<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
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
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'category'   => 'nullable|integer|exists:expense_categories,id',
            'search'     => 'nullable|string|max:200',
        ]);

        [$storeId, $store] = $this->resolveStore();

        ExpenseCategory::ensureDefaults($storeId);

        $query = Expense::forStore($storeId)->with('category', 'creator');

        if ($request->filled('start_date')) {
            $query->where('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('expense_date', '<=', $request->end_date);
        }
        if ($request->filled('category')) {
            $query->where('expense_category_id', $request->category);
        }
        if ($request->filled('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('description', 'like', '%' . $escaped . '%');
        }

        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString();

        $categories = ExpenseCategory::forStore($storeId)->active()->orderBy('name')->get();

        // Monthly summary
        $now = Carbon::now();
        $monthlySummary = Expense::forStore($storeId)
            ->whereBetween('expense_date', [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()])
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->with('category')
            ->get();

        $totalThisMonth = $monthlySummary->sum('total');

        return view('handai-manager.finance.expenses.index', compact(
            'store', 'expenses', 'categories', 'monthlySummary', 'totalThisMonth'
        ));
    }

    public function create()
    {
        [$storeId, $store] = $this->resolveStore();
        ExpenseCategory::ensureDefaults($storeId);
        $categories = ExpenseCategory::forStore($storeId)->active()->orderBy('name')->get();

        return view('handai-manager.finance.expenses.create', compact('store', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date'        => 'required|date',
            'amount'              => 'required|numeric|min:1',
            'description'         => 'required|string|max:500',
            'payment_method'      => 'required|in:cash,bank_transfer,e-wallet',
        ]);

        [$storeId, $store] = $this->resolveStore();

        $category = ExpenseCategory::findOrFail($request->expense_category_id);

        DB::transaction(function () use ($request, $storeId, $category) {
            // Map payment method to COA sub-type
            $paymentSubType = $request->payment_method === 'cash'
                ? ChartOfAccount::SUB_KAS
                : ChartOfAccount::SUB_BANK;

            // Create journal entry via accounting service
            $journal = AccountingService::createJournal(
                $storeId,
                "Pengeluaran: {$request->description}",
                'MANUAL',
                [
                    [
                        'account_sub_type' => ChartOfAccount::SUB_OPERASIONAL,
                        'debit'  => (float) $request->amount,
                        'credit' => 0,
                        'memo'   => "Biaya {$category->name}: {$request->description}",
                    ],
                    [
                        'account_sub_type' => $paymentSubType,
                        'debit'  => 0,
                        'credit' => (float) $request->amount,
                        'memo'   => 'Pembayaran pengeluaran',
                    ],
                ]
            );

            Expense::create([
                'store_id'             => $storeId,
                'expense_category_id'  => $request->expense_category_id,
                'expense_date'         => $request->expense_date,
                'amount'               => $request->amount,
                'description'          => $request->description,
                'payment_method'       => $request->payment_method,
                'reference_number'     => $request->reference_number,
                'journal_id'           => $journal->id,
                'created_by'           => Auth::id(),
            ]);
        });

        return redirect()->route('manager.finance.expenses.index')
            ->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy($id)
    {
        [$storeId] = $this->resolveStore();

        $expense = Expense::where('store_id', $storeId)->findOrFail($id);
        $expense->delete();

        return redirect()->route('manager.finance.expenses.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
