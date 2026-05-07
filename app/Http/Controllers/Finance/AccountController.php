<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        return Inertia::render('Finance/Accounts', [
            'accounts' => auth()->user()->accounts()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|in:cash,bank,credit_card,e-wallet,investment,others',
            'currency' => 'required|string|size:3',
            'balance' => 'required|numeric',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
        ]);

        auth()->user()->accounts()->create([
            ...$validated,
            'initial_balance' => $validated['balance'],
        ]);

        return back()->with('success', 'Akun berhasil dibuat');
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'currency' => 'required|string|size:3',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        $account->update($validated);

        return back()->with('success', 'Akun berhasil diperbarui');
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        if ($account->transactions()->exists()) {
            $account->delete(); // Soft delete if trait added
        } else {
            $account->forceDelete();
        }

        return back()->with('success', 'Akun berhasil dihapus');
    }
}
