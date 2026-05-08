<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'ticker',
        'type',
        'units',
        'average_buy_price',
        'currency',
        'current_price',
        'platform',
    ];

    protected $casts = [
        'units' => 'float',
        'average_buy_price' => 'decimal:4',
        'current_price' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prices()
    {
        return $this->hasMany(InvestmentPrice::class);
    }

    // Accessors for P/L calculation
    public function getTotalCostAttribute()
    {
        return $this->units * $this->average_buy_price;
    }

    public function getCurrentValueAttribute()
    {
        return $this->units * ($this->current_price ?? $this->average_buy_price);
    }

    public function getProfitLossAttribute()
    {
        return $this->current_value - $this->total_cost;
    }

    public function getProfitLossPercentageAttribute()
    {
        if ($this->total_cost <= 0) return 0;
        return ($this->profit_loss / $this->total_cost) * 100;
    }
}
