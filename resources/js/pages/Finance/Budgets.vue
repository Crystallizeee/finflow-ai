<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, Edit2, PieChart, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Anggaran', href: '/budgets' },
];

const props = defineProps<{
    budgets: any[];
    categories: any[];
}>();

const isModalOpen = ref(false);
const editingBudget = ref<any>(null);

const form = useForm({
    name: '',
    category_id: '',
    amount: '',
    period: 'monthly',
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().split('T')[0],
});

const openCreateModal = () => {
    editingBudget.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (budget: any) => {
    editingBudget.value = budget;
    form.name = budget.name;
    form.category_id = budget.category_id || '';
    form.amount = budget.amount;
    form.period = budget.period;
    form.start_date = budget.start_date.split('T')[0];
    form.end_date = budget.end_date.split('T')[0];
    isModalOpen.value = true;
};

const submit = () => {
    if (editingBudget.value) {
        form.put(route('budgets.update', editingBudget.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('budgets.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteBudget = (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus anggaran ini?')) {
        useForm({}).delete(route('budgets.destroy', id));
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    editingBudget.value = null;
};

const getProgressColor = (spent: number, amount: number) => {
    const ratio = spent / amount;
    if (ratio >= 1) return 'bg-rose-500';
    if (ratio >= 0.8) return 'bg-amber-500';
    return 'bg-emerald-500';
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Anggaran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-8 max-w-7xl mx-auto w-full">
            
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-black tracking-tight flex items-center gap-2">
                        <PieChart class="h-8 w-8 text-indigo-600" />
                        Manajemen Anggaran
                    </h1>
                    <p class="text-slate-500 mt-1">Kontrol pengeluaran Anda dengan menetapkan batasan per kategori.</p>
                </div>
                <button @click="openCreateModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-indigo-100">
                    <Plus class="h-5 w-5" />
                    Buat Anggaran
                </button>
            </div>

            <!-- Budgets Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="budget in budgets" :key="budget.id" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col relative overflow-hidden group">
                    
                    <!-- Progress Bar Background (Subtle) -->
                    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" :class="getProgressColor(budget.spent, budget.amount)"></div>

                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-[10px] uppercase font-black tracking-widest px-2 py-0.5 rounded-full"
                                :class="budget.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                {{ budget.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <h3 class="text-xl font-bold mt-2 text-slate-900 dark:text-white">{{ budget.name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ budget.category?.name || 'Semua Kategori' }} • {{ budget.period }}</p>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openEditModal(budget)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                                <Edit2 class="h-4 w-4" />
                            </button>
                            <button @click="deleteBudget(budget.id)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="mt-auto pt-6">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">Terpakai</p>
                                <p class="text-lg font-black" :class="parseFloat(budget.spent) > parseFloat(budget.amount) ? 'text-rose-600' : 'text-slate-900 dark:text-white'">
                                    {{ formatCurrency(budget.spent) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">Target</p>
                                <p class="text-sm font-bold text-slate-600 dark:text-slate-400">
                                    {{ formatCurrency(budget.amount) }}
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mb-2">
                            <div class="h-full transition-all duration-1000" 
                                :class="getProgressColor(budget.spent, budget.amount)"
                                :style="{ width: Math.min(100, (budget.spent / budget.amount) * 100) + '%' }">
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-[11px] font-bold">
                            <span :class="parseFloat(budget.spent) > parseFloat(budget.amount) ? 'text-rose-500' : 'text-slate-500'">
                                {{ Math.round((budget.spent / budget.amount) * 100) }}% Terpakai
                            </span>
                            <span v-if="parseFloat(budget.spent) <= parseFloat(budget.amount)" class="text-emerald-500 flex items-center gap-1">
                                <CheckCircle2 class="h-3 w-3" /> Sisa {{ formatCurrency(budget.amount - budget.spent) }}
                            </span>
                            <span v-else class="text-rose-500 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" /> Over Budget {{ formatCurrency(budget.spent - budget.amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="budgets.length === 0" @click="openCreateModal" class="cursor-pointer border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-3xl p-12 flex flex-col items-center justify-center text-center hover:border-indigo-500 hover:bg-indigo-50/30 transition-all group">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                        <Plus class="h-8 w-8 text-slate-300 group-hover:text-indigo-600" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Belum ada anggaran</h3>
                    <p class="text-slate-500 text-sm mt-1">Buat batasan pengeluaran pertama Anda untuk mulai berhemat.</p>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <TransitionRoot appear :show="isModalOpen" as="template">
                <Dialog as="div" @close="closeModal" class="relative z-50">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
                    </TransitionChild>

                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                                <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-[2.5rem] bg-white dark:bg-slate-900 p-8 shadow-2xl transition-all border border-slate-100 dark:border-slate-800">
                                    <DialogTitle as="h3" class="text-2xl font-black text-slate-900 dark:text-white mb-6">
                                        {{ editingBudget ? 'Edit Anggaran' : 'Buat Anggaran Baru' }}
                                    </DialogTitle>

                                    <form @submit.prevent="submit" class="space-y-5">
                                        <div>
                                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Nama Anggaran</label>
                                            <input v-model="form.name" type="text" placeholder="Contoh: Makan Bulanan" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4 focus:ring-2 focus:ring-indigo-500" required />
                                        </div>

                                        <div>
                                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Kategori (Opsional)</label>
                                            <select v-model="form.category_id" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4 focus:ring-2 focus:ring-indigo-500">
                                                <option value="">Semua Kategori</option>
                                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Jumlah Target</label>
                                                <input v-model="form.amount" type="number" placeholder="Rp 0" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4 focus:ring-2 focus:ring-indigo-500" required />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Periode</label>
                                                <select v-model="form.period" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4 focus:ring-2 focus:ring-indigo-500">
                                                    <option value="weekly">Mingguan</option>
                                                    <option value="monthly">Bulanan</option>
                                                    <option value="quarterly">Kuartal</option>
                                                    <option value="yearly">Tahunan</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Tgl Mulai</label>
                                                <input v-model="form.start_date" type="date" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4 focus:ring-2 focus:ring-indigo-500" required />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Tgl Berakhir</label>
                                                <input v-model="form.end_date" type="date" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4 focus:ring-2 focus:ring-indigo-500" required />
                                            </div>
                                        </div>

                                        <div class="flex gap-3 mt-8">
                                            <button type="button" @click="closeModal" class="flex-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 py-3.5 rounded-2xl font-bold hover:bg-slate-200 transition-colors">Batal</button>
                                            <button type="submit" :disabled="form.processing" class="flex-1 bg-indigo-600 text-white py-3.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 disabled:opacity-50">
                                                {{ editingBudget ? 'Simpan Perubahan' : 'Buat Anggaran' }}
                                            </button>
                                        </div>
                                    </form>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>

        </div>
    </AppLayout>
</template>
