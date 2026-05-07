<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'storage_path',
        'status',
        'merchant_name',
        'total_amount',
        'currency',
        'date',
        'extracted_data',
        'confidence_score',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'total_amount' => 'decimal:2',
        'confidence_score' => 'float',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function items()
    {
        return $this->hasMany(ReceiptItem::class);
    }
}
