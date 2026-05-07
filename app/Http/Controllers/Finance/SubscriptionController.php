<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $subscriptions = $user->subscriptions()
            ->with('category')
            ->orderBy('next_billing_date', 'asc')
            ->get();

        $categories = $user->categories()->where('type', 'expense')->get();

        return Inertia::render('Finance/Subscriptions', [
            'subscriptions' => $subscriptions->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'merchant' => $s->merchant,
                'amount' => (float) $s->amount,
                'currency' => $s->currency,
                'billing_cycle' => $s->billing_cycle,
                'next_billing_date' => $s->next_billing_date->format('d M Y'),
                'is_active' => $s->is_active,
                'category' => $s->category->name,
                'detected_by_ai' => $s->detected_by_ai,
            ]),
            'categories' => $categories,
            'stats' => [
                'monthly_total' => (float) $subscriptions->where('is_active', true)->sum('amount'), // simplified
                'active_count' => $subscriptions->where('is_active', true)->count(),
            ],
            'aiSuggestions' => $this->getAiSuggestions($user),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'merchant' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'category_id' => 'required|exists:categories,id',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,yearly',
            'next_billing_date' => 'required|date',
        ]);

        $request->user()->subscriptions()->create($validated);

        return redirect()->back()->with('success', 'Subscription added successfully');
    }

    public function toggle(Subscription $subscription)
    {
        if ($subscription->user_id !== auth()->id()) abort(403);
        
        $subscription->update(['is_active' => !$subscription->is_active]);
        return redirect()->back();
    }

    private function getAiSuggestions($user)
    {
        // For now, mock suggestions - later we will use real AI logic
        return [
            [
                'name' => 'Netflix',
                'merchant' => 'Netflix.com',
                'amount' => 186000,
                'confidence' => 0.98,
                'reason' => 'Ditemukan 3 transaksi serupa setiap tanggal 15'
            ],
            [
                'name' => 'Spotify',
                'merchant' => 'Spotify Premium',
                'amount' => 54990,
                'confidence' => 0.95,
                'reason' => 'Ditemukan transaksi rutin bulanan'
            ]
        ];
    }
}
