<?php

namespace App\Services\AI;

use App\Models\Receipt;
use App\Models\User;
use App\Services\AI\AIProviderFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptService
{
    public function __construct(
        private readonly AIProviderFactory $aiFactory,
        private readonly \App\Services\Finance\TransactionService $transactionService
    ) {}

    public function process(User $user, UploadedFile $file): Receipt
    {
        \Illuminate\Support\Facades\Log::info("Starting receipt processing for user: " . $user->id);

        // 1. Store the file
        $path = $file->store("receipts/{$user->ulid}", 'public');
        \Illuminate\Support\Facades\Log::info("Receipt stored at: " . $path);

        // 2. Create database record
        $receipt = Receipt::create([
            'user_id' => $user->id,
            'storage_path' => $path,
            'status' => 'processing',
        ]);

        // 3. Prepare image for AI
        $base64 = base64_encode(file_get_contents($file->getRealPath()));
        $mimeType = $file->getMimeType();

        try {
            // 4. Call AI Provider
            \Illuminate\Support\Facades\Log::info("Requesting AI provider...");
            $provider = $this->aiFactory->getProvider('gemini_direct');
            \Illuminate\Support\Facades\Log::info("Using provider: " . $provider->getProviderName());
            
            $data = $provider->parseReceipt($base64, $mimeType);
            \Illuminate\Support\Facades\Log::info("AI extracted data: " . json_encode($data));

            // 5. Save extracted data to receipt — transaction will be created via confirm flow
            $currency = $data['currency'] ?? 'IDR';
            $receipt->update([
                'status'           => 'completed',
                'merchant_name'    => $data['merchant_name'] ?? null,
                'total_amount'     => $data['total_amount'] ?? 0,
                'currency'         => $currency,
                'date'             => $data['date'] ?? now()->toDateString(),
                'extracted_data'   => $data,
                'confidence_score' => 0.95,
            ]);

            // 6. Persist individual items to receipt_items table
            if (!empty($data['items']) && is_array($data['items'])) {
                $receipt->items()->delete(); // clear if re-processing
                $itemRows = [];
                foreach ($data['items'] as $item) {
                    $qty = (float) ($item['quantity'] ?? 1);
                    $ppu = (float) ($item['price_per_unit'] ?? $item['price'] ?? 0);
                    $disc = (float) ($item['discount'] ?? 0);
                    $total = (float) ($item['total_price'] ?? ($ppu * $qty) - $disc);

                    $itemRows[] = [
                        'receipt_id'     => $receipt->id,
                        'name'           => $item['name'] ?? 'Unknown Item',
                        'price_per_unit' => $ppu,
                        'quantity'       => $qty,
                        'discount'       => $disc,
                        'total_price'    => $total,
                        'currency'       => $currency,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }
                \App\Models\ReceiptItem::insert($itemRows);
                \Illuminate\Support\Facades\Log::info("Saved " . count($itemRows) . " receipt items for receipt: " . $receipt->id);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Receipt processing failed: " . $e->getMessage());
            $receipt->update(['status' => 'failed']);
            throw $e;
        }

        return $receipt;
    }
}
