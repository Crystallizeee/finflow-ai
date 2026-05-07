<?php

namespace App\Events;

use App\Models\Budget;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetExceeded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Budget $budget
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->budget->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'budget.exceeded';
    }

    public function broadcastWith(): array
    {
        return [
            'budget_name' => $this->budget->name,
            'amount' => $this->budget->amount,
            'spent' => $this->budget->spent,
            'message' => "Anggaran '{$this->budget->name}' telah melampaui batas!",
        ];
    }
}
