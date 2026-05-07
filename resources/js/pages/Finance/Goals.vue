<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Award, Plus, Target, Calendar, TrendingUp, ChevronRight, CheckCircle2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    goals: Array<{
        id: string;
        name: string;
        target_amount: number;
        current_amount: number;
        progress: number;
        target_date: string;
        icon: string;
        color: string;
        is_completed: boolean;
    }>
}>();

const showAddModal = ref(false);

const form = useForm({
    name: '',
    target_amount: 0,
    target_date: '',
    description: '',
});

const submit = () => {
    form.post(route('goals.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const breadcrumbs = [
    { title: 'Target Keuangan', href: '/goals' }
];
</script>

<template>
    <Head title="Target Keuangan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-10 max-w-6xl mx-auto w-full">
            <!-- Header -->
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                        <Award class="w-10 h-10 text-amber-500" />
                        Target Keuangan
                    </h1>
                    <p class="text-slate-500 mt-2">Wujudkan impian finansial Anda dengan bantuan AI.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-all shadow-lg shadow-indigo-200 dark:shadow-none"
                >
                    <Plus class="w-5 h-5" />
                    Target Baru
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="goals.length === 0" class="bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[2.5rem] p-16 text-center">
                <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <Target class="w-12 h-12 text-slate-300" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Belum ada target?</h3>
                <p class="text-slate-500 mb-8 max-w-sm mx-auto">Mulai buat target tabungan Anda, dan AI kami akan membantu menghitung strategi terbaik untuk mencapainya.</p>
                <button @click="showAddModal = true" class="text-indigo-600 font-bold hover:underline">Buat Target Pertama Anda →</button>
            </div>

            <!-- Goals Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div 
                    v-for="goal in goals" 
                    :key="goal.id"
                    class="group bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl hover:shadow-2xl transition-all overflow-hidden"
                >
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl" :style="{ backgroundColor: goal.color + '20' }">
                                {{ goal.icon }}
                            </div>
                            <div v-if="goal.is_completed" class="bg-emerald-100 text-emerald-600 p-2 rounded-full">
                                <CheckCircle2 class="w-6 h-6" />
                            </div>
                        </div>

                        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">{{ goal.name }}</h3>
                        <p class="text-slate-400 text-sm font-medium mb-6 flex items-center gap-1">
                            <Calendar class="w-4 h-4" /> s/d {{ goal.target_date }}
                        </p>

                        <!-- Progress Bar -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm font-bold mb-2">
                                <span class="text-slate-500">Progress</span>
                                <span class="text-indigo-600">{{ goal.progress }}%</span>
                            </div>
                            <div class="w-full h-4 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-indigo-600 transition-all duration-1000 ease-out" 
                                    :style="{ width: goal.progress + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Terkumpul</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white">{{ formatCurrency(goal.current_amount) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Target</p>
                                <p class="text-sm font-bold text-slate-500">{{ formatCurrency(goal.target_amount) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 flex justify-between items-center group-hover:bg-indigo-600 transition-colors group">
                        <p class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-white transition-colors">Lihat Strategi AI</p>
                        <ChevronRight class="w-5 h-5 text-slate-400 group-hover:text-white transition-all transform group-hover:translate-x-1" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal (Simplified) -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[2.5rem] p-10 shadow-2xl">
                <h2 class="text-2xl font-black mb-6">Buat Target Baru</h2>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Nama Target</label>
                        <input v-model="form.name" type="text" placeholder="Misal: Beli MacBook Air" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Jumlah Target</label>
                            <input v-model="form.target_amount" type="number" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Target Tanggal</label>
                            <input v-model="form.target_date" type="date" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-600 transition-all" />
                        </div>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showAddModal = false" class="flex-1 px-6 py-4 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 transition-all">Batal</button>
                        <button type="submit" class="flex-1 bg-indigo-600 text-white px-6 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">Simpan Target</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
