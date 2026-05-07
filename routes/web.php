<?php

use App\Http\Controllers\Finance\AccountController;
use App\Http\Controllers\Finance\TransactionController;
use App\Http\Controllers\Finance\ReceiptController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Finance\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('accounts', AccountController::class);
    Route::resource('transactions', TransactionController::class);
    Route::resource('receipts', ReceiptController::class);
    Route::post('receipts/{receipt}/confirm', [ReceiptController::class, 'confirm'])->name('receipts.confirm');

    // Budgets
    Route::resource('budgets', \App\Http\Controllers\Finance\BudgetController::class);

    // Subscriptions
    Route::resource('subscriptions', \App\Http\Controllers\Finance\SubscriptionController::class);

    // Reports
    Route::get('reports', [\App\Http\Controllers\Finance\ReportController::class, 'index'])->name('reports.index');

    // Goals
    Route::resource('goals', \App\Http\Controllers\Finance\FinancialGoalController::class);
    Route::get('analytics', [\App\Http\Controllers\Finance\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::resource('budgets', \App\Http\Controllers\Finance\BudgetController::class);
    Route::resource('subscriptions', \App\Http\Controllers\Finance\SubscriptionController::class);
    Route::patch('subscriptions/{subscription}/toggle', [\App\Http\Controllers\Finance\SubscriptionController::class, 'toggle'])->name('subscriptions.toggle');
    Route::get('recurring', [\App\Http\Controllers\Finance\RecurringTransactionController::class, 'index'])->name('recurring.index');
    Route::post('recurring', [\App\Http\Controllers\Finance\RecurringTransactionController::class, 'store'])->name('recurring.store');
    Route::patch('recurring/{recurring}/toggle', [\App\Http\Controllers\Finance\RecurringTransactionController::class, 'toggle'])->name('recurring.toggle');

    // AI Chat Assistant
    Route::get('ai-chat', [App\Http\Controllers\Finance\AIChatController::class, 'index'])->name('ai-chat.index');
    Route::post('ai-chat/send', [App\Http\Controllers\Finance\AIChatController::class, 'send'])->name('ai-chat.send');
});

require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
