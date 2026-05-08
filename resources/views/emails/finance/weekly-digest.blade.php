<x-mail::message>
# Halo, {{ $user->name }}! 👋

Berikut adalah rangkuman perjalanan finansial kamu selama 7 hari terakhir.

<x-mail::panel>
### 📈 Ringkasan Minggu Ini
- **Total Pemasukan:** Rp {{ number_format($weeklyData['income'] ?? 0, 0, ',', '.') }}
- **Total Pengeluaran:** Rp {{ number_format($weeklyData['expense'] ?? 0, 0, ',', '.') }}
- **Tabungan Bersih:** Rp {{ number_format(($weeklyData['income'] ?? 0) - ($weeklyData['expense'] ?? 0), 0, ',', '.') }}
</x-mail::panel>

### 🤖 Analisis AI FinFlow
{{ $aiCommentary }}

### 🍕 Pengeluaran Terbesar Berdasarkan Kategori
@foreach($categorySpending as $item)
- **{{ $item['category'] }}:** Rp {{ number_format($item['total'], 0, ',', '.') }}
@endforeach

Terus pantau keuangan kamu agar target masa depan makin cepat tercapai!

<x-mail::button :url="config('app.url') . '/dashboard'">
Lihat Dashboard Lengkap
</x-mail::button>

Stay smart with your money,<br>
**Tim FinFlow AI**
</x-mail::message>
