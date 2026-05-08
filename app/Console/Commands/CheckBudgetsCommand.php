<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Notifications\BudgetExceededNotification;
use Illuminate\Console\Command;

class CheckBudgetsCommand extends Command
{
    protected $signature = 'app:check-budgets';
    protected $description = 'Periksa budget pengguna dan kirim notifikasi jika melebihi ambang batas (80%)';

    public function handle()
    {
        $this->info('Memeriksa anggaran pengguna...');

        $budgets = Budget::where('is_active', true)
            ->with('user')
            ->get();

        foreach ($budgets as $budget) {
            $usage = $budget->spent / $budget->amount;

            // Jika penggunaan > 80% dan notifikasi belum dikirim hari ini
            if ($usage >= 0.8) {
                $budget->user->notify(new BudgetExceededNotification($budget));
                $this->line("Notifikasi dikirim untuk: {$budget->name} (User: {$budget->user->name})");
            }
        }

        $this->info('Pengecekan selesai.');
    }
}
