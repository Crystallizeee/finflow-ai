<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'merchant',
        'amount',
        'currency',
        'billing_cycle',
        'next_billing_date',
        'is_active',
        'detected_by_ai',
        'detection_confidence',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_billing_date' => 'date',
        'is_active' => 'boolean',
        'detected_by_ai' => 'boolean',
        'detection_confidence' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
