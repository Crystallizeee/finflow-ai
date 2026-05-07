<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { RefreshCcw, Plus, Calendar, CreditCard, ChevronRight, Power, PowerOff, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    recurring: Array<{
        id: string;
        description: string;
        amount: number;
        frequency: string;
        next_occurrence: string;
        category: string;
        account: string;
        is_active: boolean;
    }>
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const breadcrumbs = [
    { title: 'Transaksi Berulang', href: '/recurring' }
];

const toggleActive = (id: string) => {
    useForm({}).patch(route('recurring.toggle', id));
};
</script>

<template>
    <Head title="Transaksi Berulang" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-10 max-w-6xl mx-auto w-full">
            <!-- Header -->
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                        <RefreshCcw class="w-10 h-10 text-indigo-600 animate-spin-slow" />
                        Otomasi Rutin
                    </h1>
                    <p class="text-slate-500 mt-2">Biarkan AI mencatat pengeluaran rutin Anda secara otomatis.</p>
                </div>
                <button 
                    class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-all hover:scale-105 active:scale-95"
                >
                    <Plus class="w-5 h-5" />
                    Tambah Rutinitas
                </button>
            </div>

            <!-- Insight Box -->
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-3xl p-6 mb-10 flex items-start gap-4">
                <AlertCircle class="w-6 h-6 text-indigo-600 shrink-0 mt-1" />
                <div>
                    <p class="text-indigo-900 dark:text-indigo-200 font-bold mb-1">Tips Otomasi</p>
                    <p class="text-indigo-700 dark:text-indigo-300 text-sm leading-relaxed">
                        Sistem akan otomatis membuat transaksi baru pada tanggal <strong>Next Occurrence</strong>. Pastikan saldo akun Anda mencukupi untuk menghindari selisih catatan.
                    </p>
                </div>
            </div>

            <!-- Recurring List -->
            <div class="space-y-4">
                <div 
                    v-for="item in recurring" 
                    :key="item.id"
                    class="group bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 p-6 shadow-sm hover:shadow-xl transition-all flex flex-col md:flex-row items-center gap-6"
                    :class="{ 'opacity-60 grayscale-[0.5]': !item.is_active }"
                >
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0">
                        <Calendar class="w-8 h-8 text-slate-400" />
                    </div>

                    <div class="flex-1 text-center md:text-left">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">{{ item.description }}</h3>
                        <div class="flex flex-wrap justify-center md:justify-start gap-3">
                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-black uppercase tracking-widest text-slate-500">
                                {{ item.frequency }}
                            </span>
                            <span class="text-sm text-slate-400 font-medium flex items-center gap-1">
                                <CreditCard class="w-4 h-4" /> {{ item.account }}
                            </span>
                        </div>
                    </div>

                    <div class="text-center md:text-right">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Jumlah</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</p>
                    </div>

                    <div class="text-center md:text-right">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Berikutnya</p>
                        <p class="text-sm font-black text-indigo-600">{{ item.next_occurrence }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button 
                            @click="toggleActive(item.id)"
                            class="p-4 rounded-2xl transition-all"
                            :class="item.is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                        >
                            <component :is="item.is_active ? PowerOff : Power" class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="recurring.length === 0" class="text-center py-20 bg-slate-50 dark:bg-slate-900/50 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
                    <RefreshCcw class="w-16 h-16 text-slate-200 mx-auto mb-6" />
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Belum ada otomasi</h3>
                    <p class="text-slate-500 mt-2">Daftarkan tagihan rutin Anda agar tidak lupa mencatatnya.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 8s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
