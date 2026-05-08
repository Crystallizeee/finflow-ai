<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    TrendingUp, Plus, Wallet, PieChart, 
    ArrowUpRight, ArrowDownRight, 
    Bitcoin, Landmark, Coins, Briefcase, 
    ChevronRight, Info, Search
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps<{
    investments: Array<{
        id: string;
        name: string;
        ticker: string;
        type: 'stock' | 'crypto' | 'gold' | 'mutual_fund';
        units: number;
        average_buy_price: number;
        current_price: number;
        total_cost: number;
        current_value: number;
        profit_loss: number;
        profit_loss_percentage: number;
        platform: string;
    }>;
    summary: {
        total_value: number;
        total_pl: number;
        total_pl_percentage: number;
    };
    allocation: Record<string, number>;
}>();

const showAddModal = ref(false);
const searchQuery = ref('');

const filteredInvestments = computed(() => {
    return props.investments.filter(inv => 
        inv.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        inv.ticker?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const form = useForm({
    name: '',
    ticker: '',
    type: 'stock',
    units: '',
    average_buy_price: '',
    platform: '',
});

const submit = () => {
    form.post(route('investments.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const getTypeIcon = (type: string) => {
    switch (type) {
        case 'stock': return Landmark;
        case 'crypto': return Bitcoin;
        case 'gold': return Coins;
        default: return Briefcase;
    }
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'stock': return 'text-indigo-600 bg-indigo-50';
        case 'crypto': return 'text-orange-600 bg-orange-50';
        case 'gold': return 'text-amber-600 bg-amber-50';
        default: return 'text-emerald-600 bg-emerald-50';
    }
};

// Chart Data
const chartOptions = {
    chart: { type: 'donut' },
    labels: Object.keys(props.allocation).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
    colors: ['#4f46e5', '#f59e0b', '#10b981', '#f43f5e'],
    legend: { position: 'bottom' },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Total Aset',
                        formatter: () => formatCurrency(props.summary.total_value)
                    }
                }
            }
        }
    },
    dataLabels: { enabled: false }
};

const chartSeries = Object.values(props.allocation);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Investasi', href: '/investments' }
];
</script>

<template>
    <Head title="Portofolio Investasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-10 max-w-7xl mx-auto w-full space-y-8">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                        <TrendingUp class="w-10 h-10 text-indigo-600" />
                        Investment Hub
                    </h1>
                    <p class="text-slate-500 mt-2 font-medium">Pantau pertumbuhan kekayaanmu secara real-time.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-bold flex items-center gap-2 transition-all shadow-xl shadow-indigo-200 dark:shadow-none transform hover:-translate-y-1"
                >
                    <Plus class="w-6 h-6" />
                    Tambah Aset Baru
                </button>
            </div>

            <!-- Summary Cards & Chart Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Big Summary Cards -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Total Portfolio Value -->
                    <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-4 opacity-80">
                                <Wallet class="w-5 h-5" />
                                <span class="text-sm font-bold uppercase tracking-widest">Total Portofolio</span>
                            </div>
                            <h2 class="text-4xl font-black mb-2">{{ formatCurrency(summary.total_value) }}</h2>
                            <div class="flex items-center gap-2 text-indigo-100 font-bold">
                                <Info class="w-4 h-4" />
                                <span class="text-sm">Nilai aset saat ini</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total P/L -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-800 shadow-xl">
                        <div class="flex items-center gap-2 mb-4 text-slate-400">
                            <TrendingUp class="w-5 h-5" />
                            <span class="text-sm font-bold uppercase tracking-widest">Total Profit / Loss</span>
                        </div>
                        <h2 class="text-4xl font-black mb-2" :class="summary.total_pl >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                            {{ summary.total_pl >= 0 ? '+' : '' }}{{ formatCurrency(summary.total_pl) }}
                        </h2>
                        <div class="flex items-center gap-2 font-bold" :class="summary.total_pl >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                            <ArrowUpRight v-if="summary.total_pl >= 0" class="w-5 h-5" />
                            <ArrowDownRight v-else class="w-5 h-5" />
                            <span>{{ summary.total_pl_percentage.toFixed(2) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Allocation Chart -->
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-800 shadow-xl flex flex-col items-center justify-center">
                    <h3 class="text-lg font-black mb-6 self-start text-slate-900 dark:text-white">Alokasi Aset</h3>
                    <div v-if="chartSeries.length > 0" class="w-full">
                        <VueApexCharts width="100%" height="250" :options="chartOptions" :series="chartSeries" />
                    </div>
                    <div v-else class="text-center py-10 opacity-30">
                        <PieChart class="w-16 h-16 mx-auto mb-4" />
                        <p class="font-bold">Belum ada data</p>
                    </div>
                </div>
            </div>

            <!-- Assets Table Section -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Daftar Aset</h3>
                    <div class="relative w-full md:w-80">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari aset atau ticker..." 
                            class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-indigo-500 transition-all"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-slate-400 text-xs font-black uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                                <th class="p-8">Nama Aset</th>
                                <th class="p-8 text-right">Unit / Jumlah</th>
                                <th class="p-8 text-right">Harga Rata-rata</th>
                                <th class="p-8 text-right">Harga Saat Ini</th>
                                <th class="p-8 text-right">P/L (%)</th>
                                <th class="p-8 text-right">Nilai Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="inv in filteredInvestments" :key="inv.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="p-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110" :class="getTypeColor(inv.type)">
                                            <component :is="getTypeIcon(inv.type)" class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-900 dark:text-white">{{ inv.name }}</p>
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">{{ inv.ticker || inv.type }} • {{ inv.platform || 'Direct' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8 text-right font-bold text-slate-600 dark:text-slate-300">{{ inv.units }}</td>
                                <td class="p-8 text-right font-bold text-slate-600 dark:text-slate-300">{{ formatCurrency(inv.average_buy_price) }}</td>
                                <td class="p-8 text-right font-bold text-slate-900 dark:text-white">{{ formatCurrency(inv.current_price) }}</td>
                                <td class="p-8 text-right">
                                    <div class="inline-flex items-center gap-1 font-black px-3 py-1 rounded-lg" :class="inv.profit_loss >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'">
                                        <ArrowUpRight v-if="inv.profit_loss >= 0" class="w-4 h-4" />
                                        <ArrowDownRight v-else class="w-4 h-4" />
                                        {{ inv.profit_loss_percentage }}%
                                    </div>
                                </td>
                                <td class="p-8 text-right">
                                    <p class="font-black text-slate-900 dark:text-white text-lg">{{ formatCurrency(inv.current_value) }}</p>
                                    <p class="text-xs font-bold" :class="inv.profit_loss >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                                        {{ inv.profit_loss >= 0 ? '+' : '' }}{{ formatCurrency(inv.profit_loss) }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredInvestments.length === 0" class="p-20 text-center text-slate-400">
                    <Briefcase class="w-16 h-16 mx-auto mb-4 opacity-20" />
                    <p class="font-bold">Aset tidak ditemukan atau belum ada data.</p>
                </div>
            </div>
        </div>

        <!-- Add Modal (Modern Glassmorphism) -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
            <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-[2.5rem] p-10 shadow-2xl transform transition-all border border-slate-200 dark:border-slate-800">
                <h2 class="text-3xl font-black mb-2 text-slate-900 dark:text-white">Tambah Aset Baru</h2>
                <p class="text-slate-500 mb-8 font-medium">Masukkan detail aset investasi yang kamu miliki.</p>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Nama Aset / Saham</label>
                            <input v-model="form.name" type="text" placeholder="Misal: Bank BCA, Bitcoin" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all font-bold" required />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Ticker (Opsional)</label>
                            <input v-model="form.ticker" type="text" placeholder="BBCA.JK, BTC" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all font-bold uppercase" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Tipe Aset</label>
                            <select v-model="form.type" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all font-bold">
                                <option value="stock">Saham</option>
                                <option value="crypto">Crypto</option>
                                <option value="gold">Emas</option>
                                <option value="mutual_fund">Reksa Dana</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Jumlah Unit</label>
                            <input v-model="form.units" type="number" step="any" placeholder="0.00" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all font-bold" required />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Harga Beli Rata-rata</label>
                            <input v-model="form.average_buy_price" type="number" placeholder="IDR" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all font-bold" required />
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showAddModal = false" class="flex-1 px-6 py-4 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 transition-all">Batal</button>
                        <button 
                            type="submit" 
                            class="flex-1 bg-indigo-600 text-white px-6 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Aset' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom transitions and scrollbar styling if needed */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #1e293b;
}
</style>
