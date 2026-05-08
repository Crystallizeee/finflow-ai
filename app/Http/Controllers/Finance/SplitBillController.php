<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\AI\ReceiptService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SplitBillController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService
    ) {}

    public function index()
    {
        return Inertia::render('Finance/SplitBill/Index');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        // Disable time limit for AI processing
        set_time_limit(0);

        try {
            $receipt = $this->receiptService->process($request->user(), $request->file('image'));
            
            if (!$receipt || $receipt->status === 'failed') {
                throw new \Exception("AI gagal memproses struk ini. Silakan coba lagi.");
            }

            return response()->json([
                'success' => true,
                'receipt_id' => $receipt->id,
                'items' => $receipt->items()->get(['id', 'name', 'total_price']),
                'total' => $receipt->total_amount,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Split Bill Scan Failed: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request)
    {
        // Logic to save split bill results and create transactions/debt records
        // For MVP, we'll just return success after user assigns items
        return redirect()->route('dashboard')->with('success', 'Split bill berhasil dicatat! 💸');
    }
}
