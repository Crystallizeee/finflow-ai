<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import TransactionForm from '@/Components/Finance/TransactionForm.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Transaksi',
        href: '/transactions',
    },
];

defineProps<{
    transactions: any;
    accounts: any[];
    categories: any[];
}>();
</script>

<template>
    <Head title="Transaksi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 max-w-7xl mx-auto w-full md:flex-row">
            <!-- Left Side: Transaction List -->
            <div class="flex-1 space-y-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-black">Riwayat Transaksi</h1>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs font-bold uppercase tracking-widest">
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Deskripsi</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ new Date(tx.date).toLocaleDateString('id-ID') }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ tx.description || 'Tanpa Deskripsi' }}</p>
                                    <p class="text-xs text-slate-400">{{ tx.account.name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold" :style="{ backgroundColor: tx.category?.color + '20', color: tx.category?.color }">
                                        {{ tx.category?.name || 'Transfer' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black" :class="tx.type === 'income' ? 'text-green-600' : tx.type === 'expense' ? 'text-red-600' : 'text-slate-600'">
                                    {{ tx.type === 'expense' ? '-' : '+' }}{{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: tx.currency }).format(tx.amount) }}
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    Belum ada transaksi tercatat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Side: Add Transaction Form -->
            <div class="w-full md:w-96">
                <TransactionForm :accounts="accounts" :categories="categories" />
            </div>
        </div>
    </AppLayout>
</template>
