<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { RefreshCcw, Plus, Trash2, CheckCircle, AlertTriangle, Sparkles, CreditCard, Calendar } from 'lucide-vue-next';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Langganan', href: '/subscriptions' },
];

const props = defineProps<{
    subscriptions: any[];
    potentialSubscriptions: any[];
    categories: any[];
}>();

const isModalOpen = ref(false);
const form = useForm({
    name: '',
    merchant: '',
    category_id: '',
    amount: '',
    currency: 'IDR',
    billing_cycle: 'monthly',
    next_billing_date: new Date().toISOString().split('T')[0],
});

const openAddModal = (potential: any = null) => {
    if (potential) {
        form.name = potential.name;
        form.merchant = potential.merchant;
        form.category_id = potential.category_id || '';
        form.amount = potential.amount;
        form.currency = potential.currency;
        form.billing_cycle = potential.billing_cycle;
        form.next_billing_date = potential.next_billing_date;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const submit = () => {
    form.post(route('subscriptions.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const toggleActive = (sub: any) => {
    useForm({
        name: sub.name,
        next_billing_date: sub.next_billing_date,
        is_active: !sub.is_active
    }).put(route('subscriptions.update', sub.id));
};

const deleteSub = (id: string) => {
    if (confirm('Hapus langganan ini?')) {
        useForm({}).delete(route('subscriptions.destroy', id));
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const getDaysRemaining = (date: string) => {
    const diff = new Date(date).getTime() - new Date().getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
};
</script>

<template>
    <Head title="Langganan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-8 max-w-7xl mx-auto w-full">
            
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-black tracking-tight flex items-center gap-2">
                        <RefreshCcw class="h-8 w-8 text-indigo-600" />
                        Kelola Langganan
                    </h1>
                    <p class="text-slate-500 mt-1">Pantau tagihan berulang dan biaya langganan bulanan Anda.</p>
                </div>
                <button @click="openAddModal()" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-indigo-100">
                    <Plus class="h-5 w-5" />
                    Tambah Manual
                </button>
            </div>

            <!-- AI Detection Section -->
            <section v-if="potentialSubscriptions.length > 0" class="mb-10">
                <div class="bg-indigo-600 rounded-[2rem] p-6 text-white relative overflow-hidden shadow-xl">
                    <!-- Decor -->
                    <Sparkles class="absolute top-4 right-4 w-12 h-12 text-white/20" />
                    <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>

                    <div class="relative z-10">
                        <h2 class="text-xl font-black flex items-center gap-2">
                            <Sparkles class="w-5 h-5" />
                            AI Berhasil Mendeteksi Langganan Baru!
                        </h2>
                        <p class="text-indigo-100 text-sm mt-1 mb-6">Kami menemukan beberapa transaksi berulang yang mungkin adalah biaya langganan Anda.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="pot in potentialSubscriptions" :key="pot.merchant" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 flex justify-between items-center group">
                                <div>
                                    <h3 class="font-bold">{{ pot.name }}</h3>
                                    <p class="text-xs text-indigo-200">{{ formatCurrency(pot.amount) }} / {{ pot.billing_cycle === 'monthly' ? 'Bulan' : 'Minggu' }}</p>
                                </div>
                                <button @click="openAddModal(pot)" class="bg-white text-indigo-600 p-2 rounded-xl hover:bg-indigo-50 transition-colors">
                                    <Plus class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Active Subscriptions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="sub in subscriptions" :key="sub.id" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex items-center gap-6 relative group overflow-hidden">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center shrink-0 text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                        <CreditCard class="w-8 h-8" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">{{ sub.name }}</h3>
                            <span v-if="!sub.is_active" class="text-[10px] uppercase font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">Berhenti</span>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <span>{{ formatCurrency(sub.amount) }}</span>
                            <span>•</span>
                            <span>{{ sub.billing_cycle }}</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="mb-2">
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-tighter">Tagihan Berikutnya</p>
                            <p class="text-sm font-black" :class="getDaysRemaining(sub.next_billing_date) < 3 ? 'text-rose-500' : 'text-slate-900 dark:text-white'">
                                {{ new Date(sub.next_billing_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}
                                <span class="text-[10px] font-bold block">({{ getDaysRemaining(sub.next_billing_date) }} hari lagi)</span>
                            </p>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <button @click="toggleActive(sub)" class="text-xs font-bold hover:text-indigo-600 transition-colors">
                                {{ sub.is_active ? 'Matikan' : 'Aktifkan' }}
                            </button>
                            <button @click="deleteSub(sub.id)" class="text-xs font-bold text-slate-300 hover:text-rose-500 transition-colors">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="subscriptions.length === 0 && potentialSubscriptions.length === 0" class="lg:col-span-2 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[3rem] p-20 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                        <RefreshCcw class="w-10 h-10 text-slate-200" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Belum ada langganan terdeteksi</h3>
                    <p class="text-slate-500 max-w-md mx-auto mt-2">
                        Kami akan otomatis mendeteksi biaya berulang seperti Netflix, Spotify, atau tagihan internet dari riwayat transaksi Anda.
                    </p>
                    <button @click="openAddModal()" class="mt-8 px-8 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all">
                        Tambah Manual
                    </button>
                </div>
            </div>

            <!-- Add Modal -->
            <TransitionRoot appear :show="isModalOpen" as="template">
                <Dialog as="div" @close="isModalOpen = false" class="relative z-50">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
                    </TransitionChild>

                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                                <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-[2.5rem] bg-white dark:bg-slate-900 p-8 shadow-2xl transition-all border border-slate-100 dark:border-slate-800">
                                    <DialogTitle as="h3" class="text-2xl font-black text-slate-900 dark:text-white mb-6">Tambah Langganan</DialogTitle>

                                    <form @submit.prevent="submit" class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Nama Layanan</label>
                                            <input v-model="form.name" type="text" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4" placeholder="Contoh: Netflix Premium" required />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Merchant / Dealer</label>
                                            <input v-model="form.merchant" type="text" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4" placeholder="Contoh: NETFLIX.COM" required />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Kategori</label>
                                            <select v-model="form.category_id" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4" required>
                                                <option value="">Pilih Kategori</option>
                                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Jumlah Biaya</label>
                                                <input v-model="form.amount" type="number" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4" required />
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Siklus</label>
                                                <select v-model="form.billing_cycle" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4">
                                                    <option value="weekly">Mingguan</option>
                                                    <option value="monthly">Bulanan</option>
                                                    <option value="yearly">Tahunan</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Tagihan Berikutnya</label>
                                            <input v-model="form.next_billing_date" type="date" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 py-3 px-4" required />
                                        </div>

                                        <div class="flex gap-3 mt-6">
                                            <button type="button" @click="isModalOpen = false" class="flex-1 py-3 text-sm font-bold bg-slate-100 dark:bg-slate-800 rounded-2xl">Batal</button>
                                            <button type="submit" class="flex-1 py-3 text-sm font-bold bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100">Simpan</button>
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
