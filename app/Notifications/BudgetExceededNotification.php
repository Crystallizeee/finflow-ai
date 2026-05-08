<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Budget $budget
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $percentage = round(($this->budget->spent / $this->budget->amount) * 100);
        
        return (new MailMessage)
            ->subject('⚠️ Peringatan Anggaran: ' . $this->budget->name)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Pengeluaran Anda untuk anggaran "' . $this->budget->name . '" telah mencapai ' . $percentage . '%.')
            ->line('Total Terpakai: Rp ' . number_format($this->budget->spent, 0, ',', '.'))
            ->line('Batas Anggaran: Rp ' . number_format($this->budget->amount, 0, ',', '.'))
            ->action('Cek Dashboard', url('/budgets'))
            ->line('Segera tinjau pengeluaran Anda agar tetap on-track!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'budget_name' => $this->budget->name,
            'spent' => $this->budget->spent,
            'amount' => $this->budget->amount,
            'percentage' => round(($this->budget->spent / $this->budget->amount) * 100),
            'message' => 'Anggaran ' . $this->budget->name . ' hampir habis (' . round(($this->budget->spent / $this->budget->amount) * 100) . '%).',
            'type' => 'budget_alert'
        ];
    }
}
