<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Calendar, 
    CreditCard, 
    Plus, 
    Bell, 
    ShieldCheck, 
    X, 
    Loader2, 
    Zap, 
    AlertCircle, 
    Tv, 
    Music, 
    Cloud, 
    ExternalLink,
    ToggleLeft,
    ToggleRight
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    subscriptions: Array<{
        id: string;
        name: string;
        merchant: string;
        amount: number;
        currency: string;
        billing_cycle: string;
        next_billing_date: string;
        is_active: boolean;
        category: string;
        detected_by_ai: boolean;
    }>;
    categories: Array<{ id: string; name: string }>;
    stats: {
        monthly_total: number;
        active_count: number;
    };
    aiSuggestions: Array<{
        name: string;
        merchant: string;
        amount: number;
        confidence: number;
        reason: string;
    }>;
}>();

const showAddModal = ref(false);
const form = useForm({
    name: '',
    merchant: '',
    amount: '',
    currency: 'IDR',
    category_id: '',
    billing_cycle: 'monthly',
    next_billing_date: new Date().toISOString().split('T')[0],
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const breadcrumbs = [
    { title: 'Subscriptions', href: '/subscriptions' }
];

const submit = () => {
    form.post(route('subscriptions.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const toggleStatus = (id: string) => {
    router.patch(route('subscriptions.toggle', id));
};

const applySuggestion = (sug: any) => {
    form.name = sug.name;
    form.merchant = sug.merchant;
    form.amount = sug.amount.toString();
    showAddModal.value = true;
};
</script>

<template>
    <Head title="Subscription Manager" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-10 max-w-6xl mx-auto w-full">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                        <CreditCard class="w-10 h-10 text-violet-600" />
                        Subscriptions
                    </h1>
                    <p class="text-slate-500 mt-2">Pantau semua biaya langgananmu di satu tempat.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="bg-violet-600 text-white px-8 py-4 rounded-2xl font-bold flex items-center gap-2 transition-all hover:bg-violet-700 hover:scale-105 active:scale-95 shadow-xl shadow-violet-200 dark:shadow-none"
                >
                    <Plus class="w-5 h-5" />
                    Tambah Langganan
                </button>
            </div>

            <!-- Stats & AI Detection Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                <!-- Monthly Cost Card -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col justify-center">
                    <p class="text-sm font-black uppercase tracking-widest text-slate-400 mb-2">Estimasi Per Bulan</p>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white">{{ formatCurrency(stats.monthly_total) }}</h2>
                    <p class="text-xs font-bold text-emerald-500 mt-2 flex items-center gap-1">
                        <ShieldCheck class="w-3 h-3" />
                        {{ stats.active_count }} Langganan Aktif
                    </p>
                </div>

                <!-- AI Detection Card -->
                <div class="lg:col-span-2 bg-gradient-to-br from-violet-600 to-fuchsia-700 rounded-[2.5rem] p-8 text-white relative overflow-hidden group shadow-2xl">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="bg-white/20 p-2 rounded-xl backdrop-blur-md">
                                <Zap class="w-6 h-6 text-yellow-300" />
                            </div>
                            <h3 class="text-xl font-black">AI Detection Alert</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div 
                                v-for="(sug, idx) in aiSuggestions" 
                                :key="idx"
                                class="bg-white/10 backdrop-blur-md rounded-2xl p-4 flex items-center justify-between border border-white/10 hover:bg-white/20 transition-all cursor-pointer"
                                @click="applySuggestion(sug)"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                        <AlertCircle class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="font-black text-sm">{{ sug.name }}</p>
                                        <p class="text-[10px] text-violet-100">{{ sug.reason }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-sm">{{ formatCurrency(sug.amount) }}</p>
                                    <p class="text-[10px] text-violet-200">Konfirmasi →</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="sub in subscriptions" 
                    :key="sub.id"
                    class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all relative group overflow-hidden"
                    :class="{ 'opacity-60 grayscale-[0.5]': !sub.is_active }"
                >
                    <!-- Background Decoration -->
                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-150 transition-transform duration-700">
                        <Tv v-if="sub.category.toLowerCase().includes('hiburan')" class="w-24 h-24" />
                        <Music v-else-if="sub.category.toLowerCase().includes('musik')" class="w-24 h-24" />
                        <Cloud v-else class="w-24 h-24" />
                    </div>

                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center border border-slate-100 dark:border-slate-700">
                                    <span class="text-xl font-black text-violet-600">{{ sub.name.charAt(0) }}</span>
                                </div>
                                <button @click="toggleStatus(sub.id)" class="transition-colors" :class="sub.is_active ? 'text-violet-600' : 'text-slate-300'">
                                    <ToggleRight v-if="sub.is_active" class="w-8 h-8" />
                                    <ToggleLeft v-else class="w-8 h-8" />
                                </button>
                            </div>

                            <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">{{ sub.name }}</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">{{ sub.merchant }}</p>

                            <div class="space-y-3 mb-8">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-slate-400 font-bold">Biaya</span>
                                    <span class="text-lg font-black text-slate-900 dark:text-white">{{ formatCurrency(sub.amount) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-slate-400 font-bold">Siklus</span>
                                    <span class="text-xs font-black uppercase tracking-tighter bg-slate-50 dark:bg-slate-800 px-2 py-1 rounded-lg">{{ sub.billing_cycle }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-violet-400" />
                                <span class="text-[10px] font-black text-slate-400 uppercase">Tagihan Berikutnya</span>
                            </div>
                            <span class="text-[10px] font-black text-slate-900 dark:text-white">{{ sub.next_billing_date }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="subscriptions.length === 0" class="col-span-full py-20 text-center bg-slate-50 dark:bg-slate-900/50 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
                    <CreditCard class="w-20 h-20 text-slate-200 mx-auto mb-6" />
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white">Belum ada langganan</h3>
                    <p class="text-slate-500 mt-2">Tambah langgananmu atau biarkan AI kami mendeteksinya secara otomatis.</p>
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
                    <h2 class="text-3xl font-black mb-8">Tambah Langganan</h2>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Nama Layanan</label>
                            <input v-model="form.name" type="text" placeholder="Misal: Netflix" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Merchant/Domain</label>
                            <input v-model="form.merchant" type="text" placeholder="netflix.com" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Jumlah</label>
                                <input v-model="form.amount" type="number" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Kategori</label>
                                <select v-model="form.category_id" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500 text-sm">
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Siklus</label>
                                <select v-model="form.billing_cycle" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500 text-sm">
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Tgl Tagihan</label>
                                <input v-model="form.next_billing_date" type="date" class="w-full px-5 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500 text-xs" />
                            </div>
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="submit" :disabled="form.processing" class="flex-1 bg-violet-600 text-white py-4 rounded-2xl font-black hover:bg-violet-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-violet-200">
                                <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                                Simpan Langganan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
