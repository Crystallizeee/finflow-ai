<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $user = $request->user();
        $format = $request->format;
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now()->endOfMonth();
        
        $filename = "FinFlow_Report_" . $startDate->format('Ymd') . "_" . $endDate->format('Ymd');

        if ($format === 'pdf') {
            return $this->exportPdf($user, $startDate, $endDate, $filename);
        }

        return Excel::download(
            new TransactionsExport($user->id, $startDate, $endDate), 
            "{$filename}.{$format}"
        );
    }

    private function exportPdf($user, $startDate, $endDate, $filename)
    {
        $transactions = Transaction::with('category')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        $pdf = Pdf::loadView('exports.transactions_pdf', [
            'transactions' => $transactions,
            'startDate' => $startDate->format('d M Y'),
            'endDate' => $endDate->format('d M Y'),
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'user' => $user
        ]);

        return $pdf->download("{$filename}.pdf");
    }
}
