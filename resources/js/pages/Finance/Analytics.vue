<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    BarChart3, 
    TrendingUp, 
    TrendingDown, 
    Target, 
    Zap, 
    AlertCircle, 
    ArrowUpRight, 
    ArrowDownRight,
    ShoppingBag,
    History
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    monthlyTrend: {
        labels: string[];
        income: number[];
        expense: number[];
    };
    categoryDistribution: Array<{
        name: string;
        value: number;
        color: string;
    }>;
    forecast: {
        projected_end_balance: number;
        projected_spending: number;
        is_risky: boolean;
    };
    topMerchants: Array<{
        merchant: string;
        count: number;
        total: number;
    }>;
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const breadcrumbs = [
    { title: 'AI Analytics', href: '/analytics' }
];

// Helper to find highest spending category
const topCategory = computed(() => {
    return [...props.categoryDistribution].sort((a, b) => b.value - a.value)[0];
});

const totalExpense = computed(() => {
    return props.categoryDistribution.reduce((acc, curr) => acc + curr.value, 0);
});
</script>

<template>
    <Head title="AI Financial Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-10 max-w-7xl mx-auto w-full">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                    <BarChart3 class="w-10 h-10 text-indigo-600" />
                    Financial Intelligence
                </h1>
                <p class="text-slate-500 mt-2 italic font-medium">"Data adalah kompas, AI adalah nahkodanya."</p>
            </div>

            <!-- AI Forecast & Insight Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                <!-- Forecast Card -->
                <div class="lg:col-span-2 bg-gradient-to-br from-slate-900 to-indigo-950 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                    
                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12">
                        <div>
                            <div class="flex items-center gap-2 mb-6">
                                <div class="p-2 bg-indigo-500/20 rounded-xl">
                                    <Zap class="w-5 h-5 text-indigo-400" />
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest text-indigo-300">AI Cashflow Forecast</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-400 mb-1">Estimasi Saldo Akhir Bulan</h3>
                            <p class="text-5xl font-black mb-4">{{ formatCurrency(forecast.projected_end_balance) }}</p>
                            
                            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 w-fit">
                                <AlertCircle v-if="forecast.is_risky" class="w-4 h-4 text-rose-400" />
                                <TrendingDown v-else class="w-4 h-4 text-emerald-400" />
                                <span class="text-xs font-bold" :class="forecast.is_risky ? 'text-rose-400' : 'text-emerald-400'">
                                    {{ forecast.is_risky ? 'Risiko saldo menipis' : 'Kesehatan keuangan stabil' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col justify-end space-y-6">
                            <div class="bg-white/5 p-6 rounded-2xl border border-white/10">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Prediksi Pengeluaran Sisa Hari</p>
                                <p class="text-2xl font-black">{{ formatCurrency(forecast.projected_spending) }}</p>
                            </div>
                            <div class="bg-white/5 p-6 rounded-2xl border border-white/10">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Target Penghematan</p>
                                <p class="text-2xl font-black text-emerald-400">{{ formatCurrency(forecast.projected_spending * 0.1) }} <span class="text-xs text-slate-500">(10%)</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Category Card -->
                <div v-if="topCategory" class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6">Spending Terbesar</p>
                        <div class="w-16 h-16 rounded-2xl mb-4 flex items-center justify-center text-white text-2xl font-black shadow-lg" :style="{ backgroundColor: topCategory.color }">
                            {{ topCategory.name.charAt(0) }}
                        </div>
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white">{{ topCategory.name }}</h3>
                        <p class="text-sm font-bold text-slate-500 mt-2">{{ formatCurrency(topCategory.value) }} bulan ini</p>
                    </div>
                    <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-slate-400">Porsi Anggaran</span>
                            <span class="text-xs font-black">{{ Math.round((topCategory.value / totalExpense) * 100) }}%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full" :style="{ width: (topCategory.value / totalExpense * 100) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Grid Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Monthly Trend Mock Visualization -->
                <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex justify-between items-center mb-10">
                        <h3 class="text-xl font-black flex items-center gap-2">
                            <History class="w-5 h-5 text-indigo-600" />
                            Trend Pemasukan vs Pengeluaran
                        </h3>
                        <select class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold px-4 py-2 focus:ring-0">
                            <option>6 Bulan Terakhir</option>
                        </select>
                    </div>
                    
                    <!-- Visual Bars (Simplified Chart Replacement) -->
                    <div class="flex items-end justify-between h-48 gap-4 px-2">
                        <div v-for="(label, i) in monthlyTrend.labels" :key="i" class="flex-1 flex flex-col items-center gap-3 group">
                            <div class="w-full flex justify-center gap-1 items-end h-full">
                                <div class="w-3 bg-emerald-400/80 rounded-t-sm transition-all group-hover:bg-emerald-500" :style="{ height: (monthlyTrend.income[i] / 1000000 * 40) + 'px' }"></div>
                                <div class="w-3 bg-rose-400/80 rounded-t-sm transition-all group-hover:bg-rose-500" :style="{ height: (monthlyTrend.expense[i] / 1000000 * 40) + 'px' }"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ label }}</span>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-6 justify-center">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Income</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Expense</span>
                        </div>
                    </div>
                </div>

                <!-- Top Merchants List -->
                <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <h3 class="text-xl font-black flex items-center gap-2 mb-10">
                        <ShoppingBag class="w-5 h-5 text-indigo-600" />
                        Merchant Terpopuler
                    </h3>
                    
                    <div class="space-y-6">
                        <div v-for="m in topMerchants" :key="m.merchant" class="flex items-center justify-between p-4 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center font-black text-indigo-600">
                                    {{ m.merchant.charAt(0) }}
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 dark:text-white">{{ m.merchant }}</p>
                                    <p class="text-xs font-bold text-slate-400">{{ m.count }} Transaksi</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-slate-900 dark:text-white">{{ formatCurrency(m.total) }}</p>
                                <div class="flex items-center justify-end gap-1 text-[10px] font-bold text-rose-500">
                                    <ArrowUpRight class="w-3 h-3" />
                                    Tertinggi
                                </div>
                            </div>
                        </div>
                        <div v-if="topMerchants.length === 0" class="text-center py-10 text-slate-400 italic text-sm">
                            Belum ada data merchant yang cukup.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
