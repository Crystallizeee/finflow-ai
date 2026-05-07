<?php

namespace App\Services\Finance;

use App\DataTransferObjects\TransactionData;
use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function create(User $user, TransactionData $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            // Create transaction
            $transaction = Transaction::create([
                'ulid'        => (string) Str::ulid(),
                'user_id'     => $user->id,
                'account_id'  => $data->accountId,
                'category_id' => $data->categoryId,
                'transfer_account_id' => $data->transferAccountId,
                'type'        => $data->type,
                'amount'      => $data->amount,
                'base_amount' => $data->baseAmount,
                'description' => $data->description,
                'date'        => $data->date,
                'transacted_at' => $data->transactedAt,
                'tags'        => empty($data->tags) ? null : $data->tags,
                'merchant'    => $data->merchant,
                'notes'       => $data->notes,
            ]);

            // Update account balance
            $this->updateAccountBalance($transaction);

            // Update budget spent
            $this->updateBudgetSpent($user, $transaction);

            // Invalidate relevant caches (safe — may fail if Redis is unavailable)
            try {
                Cache::tags(["user:{$user->id}", 'transactions'])->flush();
                Cache::tags(["user:{$user->id}", 'dashboard'])->flush();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Cache flush failed: " . $e->getMessage());
            }

            // Broadcast realtime event (safe — may fail if broadcasting is not configured)
            try {
                broadcast(new TransactionCreated($transaction))->toOthers();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Broadcast failed: " . $e->getMessage());
            }

            return $transaction;
        });
    }

    private function updateAccountBalance(Transaction $transaction): void
    {
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $account->increment('balance', $transaction->amount);
        } elseif ($transaction->type === 'expense') {
            $account->decrement('balance', $transaction->amount);
        } elseif ($transaction->type === 'transfer' && $transaction->transfer_account_id) {
            $account->decrement('balance', $transaction->amount);
            $transaction->transferAccount()->increment('balance', $transaction->amount);
        }
    }

    private function updateBudgetSpent(User $user, Transaction $transaction): void
    {
        if ($transaction->type !== 'expense') return;

        // Update matching active budgets
        $user->budgets()
            ->where('is_active', true)
            ->where(function ($query) use ($transaction) {
                $query->whereNull('category_id')
                      ->orWhere('category_id', $transaction->category_id);
            })
            ->increment('spent', $transaction->base_amount);
    }
}
