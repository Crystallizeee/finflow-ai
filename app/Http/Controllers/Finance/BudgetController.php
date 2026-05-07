<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = $request->user()->budgets()
            ->with('category')
            ->orderBy('is_active', 'desc')
            ->orderBy('end_date', 'asc')
            ->get();

        return Inertia::render('Finance/Budgets', [
            'budgets' => $budgets,
            'categories' => Category::where('user_id', $request->user()->id)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|string|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $request->user()->budgets()->create($validated);

        return redirect()->back()->with('success', 'Anggaran berhasil dibuat.');
    }

    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|string|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
        ]);

        $budget->update($validated);

        return redirect()->back()->with('success', 'Anggaran diperbarui.');
    }

    public function destroy(Request $request, Budget $budget)
    {
        if ($budget->user_id !== $request->user()->id) {
            abort(403);
        }

        $budget->delete();

        return redirect()->back()->with('success', 'Anggaran dihapus.');
    }
}
