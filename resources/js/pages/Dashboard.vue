<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import BalanceCard from '@/Components/Dashboard/BalanceCard.vue';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { Receipt, TrendingUp, Calendar, ArrowUpRight, ArrowDownRight, Wallet } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    accounts: any[];
    recentTransactions: any[];
    chartData: any;
    categoryBreakdown: any[];
    auth: {
        user: {
            name: string;
        }
    };
}>();

const totalBalance = computed(() => {
    return props.accounts.reduce((acc, curr) => acc + parseFloat(curr.balance), 0);
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        fontFamily: 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        sparkline: { enabled: false }
    },
    colors: ['#4f46e5'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.05,
            stops: [0, 90, 100]
        }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: props.chartData.labels,
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: { show: false },
    grid: { show: false },
    tooltip: {
        theme: 'light',
        y: {
            formatter: function (val: number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)
            }
        }
    }
}));
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-4 md:p-8 max-w-7xl mx-auto w-full">
            <!-- Greeting Section -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                        {{ greeting }}, {{ auth.user.name }}!
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg">
                        Berikut ringkasan keuangan Anda hari ini.
                    </p>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Kekayaan</p>
                    <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalBalance) }}
                    </p>
                </div>
            </header>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Left Column (Main) -->
                <div class="xl:col-span-2 space-y-8">
                    
                    <!-- Spending Chart -->
                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-xl font-bold flex items-center gap-2">
                                    <TrendingUp class="w-5 h-5 text-indigo-500" />
                                    Tren Pengeluaran
                                </h2>
                                <p class="text-sm text-slate-500">30 Hari Terakhir</p>
                            </div>
                        </div>
                        <div class="h-64 w-full">
                            <VueApexCharts type="area" height="100%" :options="chartOptions" :series="props.chartData.series" />
                        </div>
                    </section>

                    <!-- Accounts Grid -->
                    <section>
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold flex items-center gap-2">
                                <Wallet class="w-5 h-5 text-slate-400" />
                                Akun Saya
                            </h2>
                            <Link href="/accounts" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                                Lihat Semua →
                            </Link>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BalanceCard 
                                v-for="account in accounts.slice(0, 4)" 
                                :key="account.id"
                                :balance="parseFloat(account.balance)"
                                :currency="account.currency"
                                :account-name="account.name"
                                :type="account.type"
                                :color="account.color"
                            />
                            
                            <Link href="/accounts" class="group relative overflow-hidden rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800 p-6 transition-all hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 min-h-[160px] flex flex-col items-center justify-center gap-3">
                                <div class="p-3 bg-slate-100 dark:bg-slate-900 rounded-2xl group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-slate-400 group-hover:text-indigo-600">Tambah Akun Baru</span>
                            </Link>
                        </div>
                    </section>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="space-y-8">
                    <!-- Top Categories -->
                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm">
                        <h2 class="text-lg font-bold mb-6">Top Pengeluaran Kategori</h2>
                        <div class="space-y-4">
                            <div v-for="cat in categoryBreakdown" :key="cat.name" class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :style="{ backgroundColor: cat.color + '20', color: cat.color }">
                                    <Receipt class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <h3 class="text-sm font-semibold truncate">{{ cat.name }}</h3>
                                        <span class="text-sm font-bold">{{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(cat.total) }}</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                                        <!-- Fake percentage for visual -->
                                        <div class="h-1.5 rounded-full" :style="{ width: Math.max(10, Math.random() * 100) + '%', backgroundColor: cat.color }"></div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!categoryBreakdown.length" class="text-center py-6 text-slate-400 text-sm">
                                Belum ada data kategori
                            </div>
                        </div>
                    </section>

                    <!-- Recent Transactions -->
                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-bold">Aktivitas Terakhir</h2>
                            <Link href="/transactions" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat Semua</Link>
                        </div>
                        
                        <div class="space-y-5">
                            <div v-for="tx in recentTransactions" :key="tx.id" class="flex gap-4 items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                                     :class="tx.type === 'income' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                                    <ArrowUpRight v-if="tx.type === 'expense'" class="w-5 h-5" />
                                    <ArrowDownRight v-else class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ tx.description }}</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ tx.category?.name || 'Tanpa Kategori' }} • {{ new Date(tx.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold" :class="tx.type === 'income' ? 'text-emerald-600' : 'text-slate-900 dark:text-white'">
                                        {{ tx.type === 'income' ? '+' : '-' }}{{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(tx.amount) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="!recentTransactions.length" class="text-center py-8">
                                <Calendar class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                                <p class="text-sm text-slate-500">Belum ada transaksi</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

