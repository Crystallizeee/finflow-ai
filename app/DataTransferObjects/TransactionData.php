<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

final readonly class TransactionData
{
    public function __construct(
        public string $accountId,
        public string $categoryId,
        public string $type,
        public float $amount,
        public float $baseAmount,
        public string $currency,
        public float $exchangeRate,
        public string $description,
        public Carbon $date,
        public Carbon $transactedAt,
        public array $tags = [],
        public ?string $merchant = null,
        public ?string $notes = null,
        public ?string $transferAccountId = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            accountId:     $data['account_id'],
            categoryId:    $data['category_id'],
            type:          $data['type'],
            amount:        (float) $data['amount'],
            baseAmount:    (float) ($data['amount'] * ($data['exchange_rate'] ?? 1.0)),
            currency:      $data['currency'] ?? 'IDR',
            exchangeRate:  (float) ($data['exchange_rate'] ?? 1.0),
            description:   $data['description'] ?? '',
            date:          Carbon::parse($data['date']),
            transactedAt:  Carbon::parse($data['transacted_at'] ?? $data['date']),
            tags:          $data['tags'] ?? [],
            merchant:      $data['merchant'] ?? null,
            notes:         $data['notes'] ?? null,
            transferAccountId: $data['transfer_account_id'] ?? null,
        );
    }
}
