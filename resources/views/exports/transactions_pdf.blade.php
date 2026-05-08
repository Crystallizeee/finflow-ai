<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan FinFlow</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1e293b; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .logo { color: #4f46e5; font-size: 24px; font-weight: bold; }
        .summary { margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th { background-color: #4f46e5; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .amount-expense { color: #e11d48; }
        .amount-income { color: #10b981; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">FinFlow AI</div>
        <div style="font-size: 14px; margin-top: 5px;">Laporan Transaksi Keuangan</div>
        <div style="font-size: 12px; color: #64748b;">Periode: {{ $startDate }} - {{ $endDate }}</div>
    </div>

    <div class="summary">
        <table style="margin-top: 0; border: none;">
            <tr style="border: none;">
                <td style="border: none;"><strong>Total Pengeluaran:</strong></td>
                <td style="border: none; text-align: right;" class="amount-expense">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Total Pemasukan:</strong></td>
                <td style="border: none; text-align: right;" class="amount-income">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Saldo Bersih:</strong></td>
                <td style="border: none; text-align: right;"><strong>Rp {{ number_format($totalIncome - $totalExpense, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Tipe</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
            <tr>
                <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                <td>{{ $tx->category->name }}</td>
                <td>{{ $tx->description }}</td>
                <td style="text-transform: capitalize;">{{ $tx->type }}</td>
                <td class="{{ $tx->type === 'expense' ? 'amount-expense' : 'amount-income' }}">
                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh FinFlow AI Assistant pada {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
