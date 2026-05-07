<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Category;
use App\Services\Finance\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $subscriptions = $request->user()->subscriptions()
            ->with('category')
            ->orderBy('next_billing_date', 'asc')
            ->get();

        $potential = $this->subscriptionService->detectPotentialSubscriptions($request->user());

        return Inertia::render('Finance/Subscriptions', [
            'subscriptions' => $subscriptions,
            'potentialSubscriptions' => $potential,
            'categories' => Category::where('user_id', $request->user()->id)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'merchant' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,yearly',
            'next_billing_date' => 'required|date',
        ]);

        $request->user()->subscriptions()->create($validated);

        return redirect()->back()->with('success', 'Langganan berhasil ditambahkan.');
    }

    public function update(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== $request->user()->id) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'is_active' => 'required|boolean',
            'next_billing_date' => 'required|date',
        ]);

        $subscription->update($validated);

        return redirect()->back()->with('success', 'Langganan diperbarui.');
    }

    public function destroy(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== $request->user()->id) abort(403);

        $subscription->delete();

        return redirect()->back()->with('success', 'Langganan dihapus.');
    }
}
