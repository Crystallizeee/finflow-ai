<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { PieChart, Plus, AlertTriangle, CheckCircle2, TrendingDown, Calendar, Tag, Loader2, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    budgets: Array<{
        id: string;
        name: string;
        amount: number;
        spent: number;
        remaining: number;
        progress: number;
        category: string;
        period: string;
        is_active: boolean;
        end_date: string;
        is_over: boolean;
    }>;
    categories: Array<{ id: string; name: string }>;
    aiRecommendation: { text: string; suggestedAmount: number } | null;
}>();

const showAddModal = ref(false);
const form = useForm({
    name: '',
    category_id: '',
    amount: '',
    period: 'monthly',
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date(new Date().setMonth(new Date().getMonth() + 1)).toISOString().split('T')[0],
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const breadcrumbs = [
    { title: 'Anggaran', href: '/budgets' }
];

const submit = () => {
    form.post(route('budgets.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const useRecommendation = () => {
    if (props.aiRecommendation) {
        form.amount = props.aiRecommendation.suggestedAmount.toString();
        form.name = 'Anggaran Bulanan AI';
        showAddModal.value = true;
    }
};
</script>

<template>
    <Head title="Smart Budgeting" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-10 max-w-6xl mx-auto w-full">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                        <PieChart class="w-10 h-10 text-indigo-600" />
                        Smart Budgets
                    </h1>
                    <p class="text-slate-500 mt-2">Kendalikan pengeluaranmu sebelum pengeluaran mengendalikanmu.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold flex items-center gap-2 transition-all hover:bg-indigo-700 hover:scale-105 active:scale-95 shadow-xl shadow-indigo-200 dark:shadow-none"
                >
                    <Plus class="w-5 h-5" />
                    Buat Anggaran
                </button>
            </div>

            <!-- AI Insight Card -->
            <div v-if="aiRecommendation" class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2.5rem] p-8 text-white mb-12 shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-xl rounded-3xl flex items-center justify-center shrink-0">
                        <TrendingDown class="w-10 h-10 text-white" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-black mb-2 flex items-center gap-2">Rekomendasi Hemat AI</h3>
                        <p class="text-indigo-100 leading-relaxed text-lg">{{ aiRecommendation.text }}</p>
                    </div>
                    <button 
                        @click="useRecommendation()"
                        class="bg-white text-indigo-600 px-6 py-3 rounded-xl font-black text-sm uppercase tracking-wider hover:bg-indigo-50 transition-colors shrink-0"
                    >
                        Terapkan Sekarang
                    </button>
                </div>
            </div>

            <!-- Budgets Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div 
                    v-for="budget in budgets" 
                    :key="budget.id"
                    class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-2xl transition-all group flex flex-col justify-between h-full"
                >
                    <div>
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ budget.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <Tag class="w-3.5 h-3.5 text-slate-400" />
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ budget.category }}</span>
                                </div>
                            </div>
                            <div class="px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-xs font-black uppercase tracking-tighter" :class="budget.is_over ? 'text-rose-500' : 'text-emerald-500'">
                                {{ budget.is_over ? 'Bocor' : 'Aman' }}
                            </div>
                        </div>

                        <div class="mb-8">
                            <div class="flex justify-between items-end mb-2">
                                <p class="text-3xl font-black text-slate-900 dark:text-white">{{ formatCurrency(budget.spent) }}</p>
                                <p class="text-sm font-bold text-slate-400">dari {{ formatCurrency(budget.amount) }}</p>
                            </div>
                            
                            <div class="w-full h-4 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div 
                                    class="h-full transition-all duration-1000 ease-out" 
                                    :class="[
                                        budget.progress >= 100 ? 'bg-rose-500' : 
                                        budget.progress >= 80 ? 'bg-amber-500' : 'bg-indigo-600'
                                    ]"
                                    :style="{ width: Math.min(budget.progress, 100) + '%' }"
                                ></div>
                            </div>
                            <div class="flex justify-between mt-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ budget.progress }}% Terpakai</span>
                                <span class="text-[10px] font-black uppercase tracking-widest" :class="budget.is_over ? 'text-rose-500' : 'text-emerald-600'">
                                    {{ budget.is_over ? 'Kelebihan ' + formatCurrency(Math.abs(budget.remaining)) : 'Sisa ' + formatCurrency(budget.remaining) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-6 border-t border-slate-50 dark:border-slate-800">
                        <Calendar class="w-4 h-4 text-slate-300" />
                        <span class="text-xs font-bold text-slate-400 italic">Berakhir {{ budget.end_date }}</span>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="budgets.length === 0" class="col-span-full py-20 text-center bg-slate-50 dark:bg-slate-900/50 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
                    <PieChart class="w-20 h-20 text-slate-200 mx-auto mb-6" />
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white">Belum ada anggaran</h3>
                    <p class="text-slate-500 mt-2">Buat anggaran pertamamu untuk mulai mengelola keuangan dengan cerdas.</p>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <Teleport to="body">
            <div v-if="showAddModal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] w-full max-w-lg p-10 shadow-2xl relative">
                    <button @click="showAddModal = false" class="absolute top-8 right-8 text-slate-400 hover:text-slate-600">
                        <X class="w-6 h-6" />
                    </button>
                    <h2 class="text-3xl font-black mb-8">Buat Anggaran Baru</h2>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Nama Anggaran</label>
                            <input v-model="form.name" type="text" placeholder="Misal: Belanja Bulanan" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Jumlah</label>
                                <input v-model="form.amount" type="number" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Kategori</label>
                                <select v-model="form.category_id" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                                    <option value="">Semua Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Mulai</label>
                                <input v-model="form.start_date" type="date" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs" />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Selesai</label>
                                <input v-model="form.end_date" type="date" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs" />
                            </div>
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="submit" :disabled="form.processing" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black hover:bg-indigo-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-200">
                                <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                                Simpan Anggaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
