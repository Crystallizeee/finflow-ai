<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import BalanceCard from '@/Components/Dashboard/BalanceCard.vue';
import { computed, onMounted } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { Receipt, TrendingUp, Calendar, ArrowUpRight, ArrowDownRight, Wallet, Sparkles } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    totalBalance: number;
    recentTransactions: any[];
    monthlySpending: any;
    categoryBreakdown: any[];
    aiInsight: string;
    activeGoals: any[];
    upcomingSubscriptions: any[];
    forecast: any;
    auth: {
        user: {
            name: string;
        }
    };
}>();

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
        toolbar: { show: false },
        zoom: { enabled: false },
        foreColor: '#64748b',
    },
    colors: ['#4f46e5'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100]
        }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: props.monthlySpending?.labels || [],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: {
                colors: '#000000',
                fontWeight: 900,
                fontSize: '12px'
            }
        }
    },
    yaxis: { 
        show: true,
        labels: {
            style: {
                colors: '#000000',
                fontWeight: 900
            }
        }
    },
    grid: {
        show: true,
        borderColor: '#e2e8f0',
        strokeDashArray: 4,
    },
    tooltip: {
        theme: 'dark',
        style: {
            fontSize: '12px',
            fontFamily: 'inherit'
        },
        y: {
            formatter: (val: number) => formatCurrency(val)
        }
    },
}));

const spendingSeries = computed(() => {
    return [{
        name: 'Pengeluaran',
        data: props.monthlySpending?.expense || []
    }];
});

const formatCurrency = (value: number, currency: string = 'IDR') => {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: currency, 
        maximumFractionDigits: 0 
    }).format(value);
};

// Trigger notification for AI Insight on load
onMounted(() => {
    if (props.aiInsight) {
        // Use a small delay for dramatic effect
        setTimeout(() => {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    type: 'info', 
                    message: props.aiInsight 
                } 
            }));
        }, 2000);
    }
});
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
                <div class="flex gap-4">
                    <div v-if="forecast" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-center min-w-[180px]">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Prediksi Saldo Akhir</p>
                            <div v-if="forecast.is_risky" class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                        </div>
                        <p class="text-xl font-black" :class="forecast.is_risky ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                            {{ formatCurrency(forecast.projected_end_balance || 0) }}
                        </p>
                        <p class="text-[9px] font-medium text-slate-400 mt-1">
                            {{ forecast.is_risky ? '⚠️ Pengeluaran kamu agak boros nih!' : '✅ Keuangan kamu aman sampai akhir bulan.' }}
                        </p>
                    </div>
                    
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 min-w-[180px]">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Kekayaan</p>
                        <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ formatCurrency(totalBalance) }}
                        </p>
                    </div>
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
                            <VueApexCharts type="area" height="100%" :options="chartOptions" :series="spendingSeries" />
                        </div>
                    </section>
                </div>

                <!-- AI Advice Widget -->
                <div class="xl:col-span-1">
                    <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white h-full relative overflow-hidden shadow-xl flex flex-col justify-between">
                        <Sparkles class="absolute -top-4 -right-4 w-24 h-24 text-white/10" />
                        <div class="relative z-10">
                            <h3 class="text-2xl font-black mb-6 flex items-center gap-2">
                                Advisor AI
                            </h3>
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20">
                                <p class="text-base leading-relaxed text-indigo-50">
                                    "{{ aiInsight || 'Sedang menganalisis kebiasaan belanja Anda...' }}"
                                </p>
                            </div>
                        </div>
                        <div class="mt-8 relative z-10">
                            <Link :href="route('ai-chat.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest bg-white text-indigo-600 px-6 py-3 rounded-2xl hover:bg-indigo-50 transition-all shadow-lg shadow-indigo-900/20">
                                Tanya Lebih Lanjut
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <!-- Recent Activity -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Aktivitas Terbaru</h3>
                            <Link :href="route('transactions.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Semua</Link>
                        </div>

                        <div class="space-y-5">
                            <div v-for="tx in recentTransactions" :key="tx.id" class="flex items-center gap-4 group">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                    :style="{ backgroundColor: tx.category?.color + '20' }">
                                    <Receipt class="w-5 h-5" :style="{ color: tx.category?.color }" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ tx.description }}</h4>
                                    <p class="text-[10px] text-slate-500">{{ new Date(tx.date).toLocaleDateString() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black" :class="tx.type === 'expense' ? 'text-rose-600' : 'text-emerald-600'">
                                        {{ tx.type === 'expense' ? '-' : '+' }}{{ formatCurrency(tx.amount, tx.currency) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Goals Widget -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Target</h3>
                            <Link :href="route('goals.index')" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700">LIHAT</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="goal in activeGoals" :key="goal.name">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate max-w-[100px]">{{ goal.icon }} {{ goal.name }}</span>
                                    <span class="text-[10px] font-black text-indigo-600">{{ goal.progress }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-600" :style="{ width: goal.progress + '%' }"></div>
                                </div>
                            </div>
                            <div v-if="!activeGoals?.length" class="text-center py-4">
                                <p class="text-[10px] text-slate-400 italic">Kosong</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions Widget (NEW) -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Langganan</h3>
                            <Link :href="route('subscriptions.index')" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700">LIHAT</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="sub in upcomingSubscriptions" :key="sub.name" class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ sub.name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ sub.next_date }}</p>
                                </div>
                                <p class="text-xs font-black text-rose-600">{{ formatCurrency(sub.amount) }}</p>
                            </div>
                            <div v-if="!upcomingSubscriptions?.length" class="text-center py-4">
                                <p class="text-[10px] text-slate-400 italic">Kosong</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-6">Kategori</h3>
                        <div class="space-y-4">
                            <div v-for="cat in categoryBreakdown.slice(0, 3)" :key="cat.name">
                                <div class="flex justify-between text-[10px] font-bold mb-1">
                                    <span class="text-slate-600 dark:text-slate-400 truncate max-w-[80px]">{{ cat.name }}</span>
                                    <span class="text-slate-900 dark:text-white">{{ formatCurrency(cat.total) }}</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full" :style="{ width: '70%', backgroundColor: cat.color }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
