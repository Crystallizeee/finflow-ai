<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class RecurringTransactionController extends Controller
{
    public function index(Request $request)
    {
        $recurring = $request->user()->recurringTransactions()
            ->with(['category', 'account'])
            ->latest()
            ->get();

        return Inertia::render('Finance/Recurring', [
            'recurring' => $recurring->map(fn($r) => [
                'id' => $r->id,
                'description' => $r->description,
                'amount' => (float) $r->amount,
                'frequency' => $r->frequency,
                'next_occurrence' => $r->next_occurrence->format('Y-m-d'),
                'category' => $r->category?->name,
                'account' => $r->account?->name,
                'is_active' => $r->is_active,
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
        ]);

        $validated['next_occurrence'] = $validated['start_date'];

        $request->user()->recurringTransactions()->create($validated);

        return redirect()->back();
    }

    public function toggle(RecurringTransaction $recurring)
    {
        $recurring->update(['is_active' => !$recurring->is_active]);
        return redirect()->back();
    }
}
