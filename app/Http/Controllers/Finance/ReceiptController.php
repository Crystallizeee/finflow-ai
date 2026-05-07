<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Services\AI\ReceiptService;
use App\Services\Finance\TransactionService;
use App\DataTransferObjects\TransactionData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
        private readonly TransactionService $transactionService,
    ) {}

    public function index()
    {
        $user = auth()->user();
        return Inertia::render('Finance/Receipts', [
            'receipts'   => $user->receipts()->with('items')->latest()->paginate(20),
            'accounts'   => $user->accounts()->select('id', 'name', 'balance', 'currency')->get(),
            'categories' => $user->categories()->select('id', 'name', 'type')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receipt' => 'required|image|max:5120', // 5MB limit
        ]);

        try {
            $receipt = $this->receiptService->process(auth()->user(), $request->file('receipt'));
            return back()->with('success', 'Struk berhasil diproses oleh AI!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses struk: ' . $e->getMessage());
        }
    }

    public function show(Receipt $receipt)
    {
        if ($receipt->user_id !== auth()->id()) abort(403);
        return response()->json($receipt);
    }

    public function confirm(Request $request, Receipt $receipt)
    {
        if ($receipt->user_id !== auth()->id()) abort(403);

        $request->validate([
            'account_id'  => 'required|string',
            'category_id' => 'required|string',
            'type'        => 'required|in:expense,income',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'date'        => 'required|date',
            'items'       => 'nullable|array',
            'items.*.name' => 'required|string',
            'items.*.category_id' => 'nullable|string',
            'items.*.price_per_unit' => 'required|numeric',
            'items.*.quantity' => 'required|numeric',
            'items.*.discount' => 'required|numeric',
            'items.*.total_price' => 'required|numeric',
        ]);

        $user = auth()->user();

        try {
            // Validate date has a partition (not too old)
            $parsedDate = Carbon::parse($request->date);
            if ($parsedDate->lt(now()->subYears(2))) {
                $parsedDate = now();
            }

            $transactionData = TransactionData::fromRequest([
                'account_id'  => $request->account_id,
                'category_id' => $request->category_id,
                'type'        => $request->type,
                'amount'      => $request->amount,
                'description' => $request->description,
                'date'        => $parsedDate->toDateString(),
                'merchant'    => $receipt->merchant_name,
            ]);

            $transaction = $this->transactionService->create($user, $transactionData);

            // Sync edited items if provided
            if ($request->has('items')) {
                $receipt->items()->delete();
                foreach ($request->items as $item) {
                    $receipt->items()->create([
                        'name' => $item['name'],
                        'category_id' => $item['category_id'],
                        'price_per_unit' => $item['price_per_unit'],
                        'quantity' => $item['quantity'],
                        'discount' => $item['discount'],
                        'total_price' => $item['total_price'],
                        'currency' => $receipt->currency ?? 'IDR',
                    ]);
                }
            }

            $receipt->update(['transaction_id' => $transaction->id]);

            return back()->with('success', 'Transaksi berhasil dibuat!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage());
        }
    }
}
