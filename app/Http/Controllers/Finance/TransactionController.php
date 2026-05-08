<?php

namespace App\Http\Controllers\Finance;

use App\DataTransferObjects\TransactionData;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Finance\TransactionService;
use App\Services\Finance\AchievementService;
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

    public function store(Request $request, AchievementService $achievementService)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:expense,income,transfer',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'transfer_account_id' => 'required_if:type,transfer|nullable|exists:accounts,id',
        ]);

        $data = TransactionData::fromRequest($validated);
        
        $this->transactionService->create(auth()->user(), $data);
        
        // Check for new achievements
        $achievementService->checkAchievements(auth()->user());

        return back()->with('success', 'Transaksi berhasil ditambahkan');
    }

    public function destroy(Transaction $transaction)
    {
        // Simple security check
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus');
    }
}
