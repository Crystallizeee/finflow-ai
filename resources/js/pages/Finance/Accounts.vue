<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Wallet, Plus, CreditCard, Landmark, Wallet2 } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Manajemen Akun',
        href: '/accounts',
    },
];

defineProps<{
    accounts: any[];
}>();

const form = useForm({
    name: '',
    type: 'cash',
    currency: 'IDR',
    balance: 0,
    color: '#6366f1',
    icon: 'wallet',
});

const submit = () => {
    form.post(route('accounts.store'), {
        onSuccess: () => form.reset(),
    });
};

const getIcon = (type: string) => {
    switch (type) {
        case 'bank': return Landmark;
        case 'credit_card': return CreditCard;
        case 'e-wallet': return Wallet2;
        default: return Wallet;
    }
};
</script>

<template>
    <Head title="Manajemen Akun" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 max-w-7xl mx-auto w-full">
            <header class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black tracking-tight">Akun Saya</h1>
                    <p class="text-slate-500 mt-1">Kelola rekening bank, dompet, dan aset Anda.</p>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Accounts List -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="account in accounts" 
                        :key="account.id"
                        class="p-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group"
                    >
                        <div class="relative z-10">
                            <div class="flex justify-between items-start">
                                <div class="p-3 rounded-2xl" :style="{ backgroundColor: account.color + '20', color: account.color }">
                                    <component :is="getIcon(account.type)" class="h-6 w-6" />
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-slate-50 dark:bg-slate-800 px-2 py-1 rounded-md">
                                    {{ account.type }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold mt-4 text-slate-900 dark:text-white">{{ account.name }}</h3>
                            <p class="text-2xl font-black mt-1 text-indigo-600">
                                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: account.currency }).format(account.balance) }}
                            </p>
                        </div>
                        
                        <!-- Decorative background icon -->
                        <component :is="getIcon(account.type)" class="absolute -right-4 -bottom-4 h-24 w-24 text-slate-50 dark:text-slate-800/50 -rotate-12 transition-transform group-hover:scale-110" />
                    </div>

                    <div v-if="accounts.length === 0" class="col-span-full py-20 text-center text-slate-400 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800">
                        Belum ada akun. Silakan tambah akun baru.
                    </div>
                </div>

                <!-- Add Account Form -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-xl h-fit">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <Plus class="h-5 w-5 text-indigo-600" />
                        Tambah Akun
                    </h2>
                    
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-400">Nama Akun</label>
                            <input v-model="form.name" type="text" placeholder="BCA, Dompet, dll." class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent" required />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-400">Jenis</label>
                            <select v-model="form.type" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent">
                                <option value="cash">Tunai</option>
                                <option value="bank">Bank</option>
                                <option value="e-wallet">E-Wallet</option>
                                <option value="credit_card">Kartu Kredit</option>
                                <option value="investment">Investasi</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-400">Saldo Awal</label>
                            <input v-model="form.balance" type="number" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent" required />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-400">Warna</label>
                            <div class="flex gap-2">
                                <button v-for="c in ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4']" :key="c" type="button" @click="form.color = c" class="h-8 w-8 rounded-full border-2" :style="{ backgroundColor: c, borderColor: form.color === c ? 'white' : 'transparent' }"></button>
                            </div>
                        </div>

                        <button type="submit" class="w-full h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 dark:shadow-none transition-all mt-4" :disabled="form.processing">
                            Simpan Akun
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
