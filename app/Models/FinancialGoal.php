<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialGoal extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'target_date',
        'monthly_contribution',
        'icon',
        'color',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'monthly_contribution' => 'decimal:2',
        'target_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getProgressAttribute(): float
    {
        if ($this->target_amount <= 0) return 0;
        return round(($this->current_amount / $this->target_amount) * 100, 2);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }
}
