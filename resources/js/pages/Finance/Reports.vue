<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BookOpen, TrendingUp, TrendingDown, Award, Calendar, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    report: {
        period: string;
        summary: {
            income: number;
            expense: number;
            top_category: string;
            budget_utilization: number;
        };
        ai_analysis: string;
        generated_at: string;
    }
}>();

const breadcrumbs = [
    { title: 'Laporan', href: '/reports' }
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const healthScore = computed(() => {
    // Simple logic to extract score if AI provided it in a specific format or fallback
    const match = props.report.ai_analysis.match(/Skor:?\s*(\d+)/i);
    return match ? parseInt(match[1]) : 85;
});

const healthColor = computed(() => {
    if (healthScore.value > 80) return 'text-emerald-500';
    if (healthScore.value > 50) return 'text-amber-500';
    return 'text-rose-500';
});
</script>

<template>
    <Head title="Laporan Keuangan AI" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-8 max-w-5xl mx-auto w-full">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                        <BookOpen class="w-10 h-10 text-indigo-600" />
                        Analisis Keuangan
                    </h1>
                    <p class="text-slate-500 mt-2 text-lg">Periode: <span class="font-bold text-slate-900 dark:text-white">{{ report.period }}</span></p>
                </div>
                <div class="flex items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Financial Health</p>
                        <p class="text-3xl font-black mt-1" :class="healthColor">{{ healthScore }}/100</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                        <Award class="w-6 h-6 text-indigo-600" />
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-emerald-50 dark:bg-emerald-950/20 p-6 rounded-[2rem] border border-emerald-100 dark:border-emerald-900/30">
                    <TrendingUp class="w-8 h-8 text-emerald-600 mb-4" />
                    <p class="text-sm font-bold text-emerald-600/70 uppercase tracking-wider">Pemasukan</p>
                    <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ formatCurrency(report.summary.income) }}</p>
                </div>
                <div class="bg-rose-50 dark:bg-rose-950/20 p-6 rounded-[2rem] border border-rose-100 dark:border-rose-900/30">
                    <TrendingDown class="w-8 h-8 text-rose-600 mb-4" />
                    <p class="text-sm font-bold text-rose-600/70 uppercase tracking-wider">Pengeluaran</p>
                    <p class="text-2xl font-black text-rose-700 dark:text-rose-400">{{ formatCurrency(report.summary.expense) }}</p>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-950/20 p-6 rounded-[2rem] border border-indigo-100 dark:border-indigo-900/30">
                    <PieChart class="w-8 h-8 text-indigo-600 mb-4" />
                    <p class="text-sm font-bold text-indigo-600/70 uppercase tracking-wider">Budget Terpakai</p>
                    <p class="text-2xl font-black text-indigo-700 dark:text-indigo-400">{{ report.summary.budget_utilization }}%</p>
                </div>
            </div>

            <!-- AI Analysis Content -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden mb-10">
                <div class="bg-indigo-600 p-6 text-white flex justify-between items-center">
                    <h2 class="text-xl font-black flex items-center gap-2">
                        <Bot class="w-6 h-6" />
                        AI Insights & Rekomendasi
                    </h2>
                    <span class="text-xs font-bold text-indigo-100 uppercase tracking-widest">Powered by Gemini</span>
                </div>
                <div class="p-8 md:p-12 prose dark:prose-invert prose-indigo max-w-none">
                    <div v-html="report.ai_analysis.replace(/\n/g, '<br>')" class="text-slate-700 dark:text-slate-300 leading-relaxed text-lg"></div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <p class="text-xs text-slate-400 font-medium italic">Diproduksi pada: {{ report.generated_at }}</p>
                    <button class="text-sm font-bold text-indigo-600 flex items-center gap-1 hover:gap-2 transition-all">
                        Unduh PDF <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <h3 class="text-lg font-black mb-6">Top Spending</h3>
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                            <span class="text-3xl">🛍️</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-400 uppercase">Kategori Utama</p>
                            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ report.summary.top_category }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <h3 class="text-lg font-black mb-6">Tips Hemat</h3>
                    <p class="text-slate-600 dark:text-slate-400 italic">"Coba kurangi pengeluaran di kategori {{ report.summary.top_category }} sebesar 10% bulan depan untuk menabung lebih banyak."</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(br) {
    display: block;
    margin-bottom: 0.5rem;
    content: "";
}
</style>
