<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'ulid',
        'user_id',
        'account_id',
        'category_id',
        'transfer_account_id',
        'recurring_transaction_id',
        'receipt_id',
        'type',
        'amount',
        'currency',
        'exchange_rate',
        'base_amount',
        'description',
        'notes',
        'merchant',
        'location',
        'tags',
        'date',
        'transacted_at',
        'is_verified',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_amount' => 'decimal:2',
        'tags' => 'array',
        'date' => 'date',
        'transacted_at' => 'datetime',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->ulid)) {
                $transaction->ulid = (string) Str::ulid();
            }
        });

        static::created(function ($transaction) {
            $transaction->updateRelatedBudgets();
        });

        static::updated(function ($transaction) {
            $transaction->updateRelatedBudgets();
        });

        static::deleted(function ($transaction) {
            $transaction->updateRelatedBudgets();
        });
    }

    public function updateRelatedBudgets()
    {
        if ($this->type !== 'expense') return;

        // Find budgets that cover this transaction's date and category
        $budgets = Budget::where('user_id', $this->user_id)
            ->where('is_active', true)
            ->where('start_date', '<=', $this->date)
            ->where('end_date', '>=', $this->date)
            ->where(function ($query) {
                $query->whereNull('category_id')
                      ->orWhere('category_id', $this->category_id);
            })
            ->get();

        foreach ($budgets as $budget) {
            $totalSpent = Transaction::where('user_id', $this->user_id)
                ->where('type', 'expense')
                ->whereBetween('date', [$budget->start_date, $budget->end_date])
                ->where(function ($query) use ($budget) {
                    if ($budget->category_id) {
                        $query->where('category_id', $budget->category_id);
                    }
                })
                ->sum('base_amount');

            $budget->update(['spent' => $totalSpent]);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transferAccount()
    {
        return $this->belongsTo(Account::class, 'transfer_account_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
}
