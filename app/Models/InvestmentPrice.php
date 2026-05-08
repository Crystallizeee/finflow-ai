<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentPrice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'investment_id',
        'price',
        'recorded_at',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'recorded_at' => 'datetime',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
