<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReceiptItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'receipt_id',
        'category_id',
        'name',
        'price_per_unit',
        'quantity',
        'discount',
        'total_price',
        'currency',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'quantity'       => 'decimal:3',
        'discount'       => 'decimal:2',
        'total_price'    => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
