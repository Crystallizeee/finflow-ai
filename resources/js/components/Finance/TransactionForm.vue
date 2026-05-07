<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref } from 'vue';

interface Props {
    accounts: any[];
    categories: any[];
}

const props = defineProps<Props>();

const form = useForm({
    account_id: '',
    category_id: '',
    type: 'expense',
    amount: '',
    description: '',
    date: new Date().toISOString().substr(0, 10),
    transfer_account_id: '',
    merchant: '',
});

const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            form.reset();
            // Close modal if applicable
        },
    });
};
</script>

<template>
    <div class="p-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-xl max-w-md w-full">
        <h2 class="text-2xl font-black mb-6">Tambah Transaksi</h2>
        
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Type Selector -->
            <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
                <button 
                    v-for="type in ['expense', 'income', 'transfer']" 
                    :key="type"
                    type="button"
                    @click="form.type = type"
                    class="flex-1 py-2 text-sm font-bold capitalize rounded-lg transition-all"
                    :class="form.type === type ? 'bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-slate-500'"
                >
                    {{ type }}
                </button>
            </div>

            <!-- Amount -->
            <div class="space-y-2">
                <Label for="amount">Jumlah</Label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                    <Input 
                        id="amount" 
                        v-model="form.amount" 
                        type="number" 
                        placeholder="0" 
                        class="pl-12 text-xl font-bold h-14 rounded-2xl"
                        required
                    />
                </div>
            </div>

            <!-- Account -->
            <div class="space-y-2">
                <Label>Pilih Akun</Label>
                <select 
                    v-model="form.account_id" 
                    class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-medium"
                    required
                >
                    <option value="" disabled>Pilih Akun Sumber</option>
                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                        {{ account.name }} ({{ account.currency }})
                    </option>
                </select>
            </div>

            <!-- Transfer Destination -->
            <div v-if="form.type === 'transfer'" class="space-y-2">
                <Label>Pilih Akun Tujuan</Label>
                <select 
                    v-model="form.transfer_account_id" 
                    class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-medium"
                    required
                >
                    <option value="" disabled>Pilih Akun Tujuan</option>
                    <option v-for="account in accounts" :key="account.id" :value="account.id" :disabled="account.id === form.account_id">
                        {{ account.name }}
                    </option>
                </select>
            </div>

            <!-- Category -->
            <div v-if="form.type !== 'transfer'" class="space-y-2">
                <Label>Kategori</Label>
                <select 
                    v-model="form.category_id" 
                    class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-medium"
                    required
                >
                    <option value="" disabled>Pilih Kategori</option>
                    <option v-for="cat in categories.filter(c => c.type === form.type)" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>

            <!-- Date -->
            <div class="space-y-2">
                <Label for="date">Tanggal</Label>
                <Input id="date" v-model="form.date" type="date" class="h-12 rounded-xl" required />
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <Label for="description">Deskripsi</Label>
                <Input id="description" v-model="form.description" placeholder="Makan malam, belanja, dll." class="h-12 rounded-xl" />
            </div>

            <Button type="submit" class="w-full h-14 rounded-2xl text-lg font-bold shadow-lg shadow-indigo-100 dark:shadow-none" :disabled="form.processing">
                Simpan Transaksi
            </Button>
        </form>
    </div>
</template>
