<?php

namespace App\Http\Controllers\Finance;

use App\DataTransferObjects\TransactionData;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Finance\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function index(Request $request)
    {
        $transactions = auth()->user()->transactions()
            ->with(['account', 'category'])
            ->latest('date')
            ->paginate(20);

        return Inertia::render('Finance/Transactions', [
            'transactions' => $transactions,
            'accounts' => auth()->user()->accounts()->get(),
            'categories' => auth()->user()->categories()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date',
            'transfer_account_id' => 'required_if:type,transfer|nullable|exists:accounts,id',
            'tags' => 'nullable|array',
            'merchant' => 'nullable|string|max:255',
        ]);

        // Security check: ensure account belongs to user
        $account = auth()->user()->accounts()->findOrFail($validated['account_id']);

        $data = TransactionData::fromRequest($validated);
        
        $this->transactionService->create(auth()->user(), $data);

        return back()->with('success', 'Transaksi berhasil ditambahkan');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        // Reverse account balance logic would go here if needed
        // For MVP, we'll just delete the transaction
        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus');
    }
}
