<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, watch } from 'vue';
import { Sparkles } from 'lucide-vue-next';

interface Props {
    accounts: any[];
    categories: any[];
}

const props = defineProps<Props>();

const isAutoAssigned = ref(false);

// Local keyword mapping for smart categorization
const categoryKeywords: Record<string, string[]> = {
    'Makanan & Minuman': ['makan', 'kopi', 'warung', 'restoran', 'gojek', 'grabfood', 'starbucks', 'indomaret', 'alfamart', 'snack', 'minum'],
    'Transportasi': ['bensin', 'pertalite', 'pertamax', 'parkir', 'ojek', 'grab', 'gojek', 'tol', 'kereta', 'tiket', 'pesawat', 'bus'],
    'Hiburan': ['netflix', 'spotify', 'bioskop', 'game', 'steam', 'topup', 'nonton', 'travel', 'liburan'],
    'Kesehatan': ['obat', 'apotek', 'dokter', 'rs', 'rumah sakit', 'klinik', 'vitamin', 'gym', 'olahraga'],
    'Pendidikan': ['buku', 'kursus', 'udemy', 'sekolah', 'kuliah', 'pelatihan'],
    'Belanja': ['shopee', 'tokopedia', 'baju', 'celana', 'sepatu', 'mall', 'supermarket', 'mall'],
    'Tagihan': ['listrik', 'air', 'pdam', 'internet', 'wifi', 'pulsa', 'asuransi', 'iuran']
};

const form = useForm({
    account_id: '',
    category_id: '',
    type: 'expense',
    amount: '',
    currency: 'IDR',
    exchange_rate: 1,
    description: '',
    date: new Date().toISOString().substr(0, 10),
    transfer_account_id: '',
    merchant: '',
});

const suggestCategory = (description: string) => {
    if (!description || description.length < 3) return;

    const desc = description.toLowerCase();
    
    for (const [catName, keywords] of Object.entries(categoryKeywords)) {
        if (keywords.some(k => desc.includes(k))) {
            const category = props.categories.find(c => c.name === catName && c.type === form.type);
            if (category) {
                form.category_id = category.id;
                isAutoAssigned.value = true;
                setTimeout(() => isAutoAssigned.value = false, 3000);
                break;
            }
        }
    }
};

// Debounce timer
let suggestTimeout: any = null;
watch(() => form.description, (newVal) => {
    clearTimeout(suggestTimeout);
    suggestTimeout = setTimeout(() => suggestCategory(newVal), 500);
});

const currencies = [
    { code: 'IDR', symbol: 'Rp' },
    { code: 'USD', symbol: '$' },
    { code: 'SGD', symbol: 'S$' },
    { code: 'EUR', symbol: '€' },
    { code: 'JPY', symbol: '¥' },
];

const getCurrencySymbol = (code: string) => {
    return currencies.find(c => c.code === code)?.symbol || '';
};

const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            form.reset();
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

            <!-- Amount & Currency -->
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2 space-y-2">
                    <Label for="amount">Jumlah</Label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">
                            {{ getCurrencySymbol(form.currency) }}
                        </span>
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
                <div class="space-y-2">
                    <Label>Currency</Label>
                    <select 
                        v-model="form.currency" 
                        class="w-full h-14 px-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-bold text-sm"
                    >
                        <option v-for="c in currencies" :key="c.code" :value="c.code">{{ c.code }}</option>
                    </select>
                </div>
            </div>

            <!-- Exchange Rate (only if not IDR) -->
            <div v-if="form.currency !== 'IDR'" class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl space-y-3">
                <div class="flex justify-between items-center">
                    <Label class="text-indigo-700 dark:text-indigo-300">Kurs (1 {{ form.currency }} = ... IDR)</Label>
                    <span class="text-[10px] font-black uppercase text-indigo-400">Auto-update active</span>
                </div>
                <Input 
                    v-model="form.exchange_rate" 
                    type="number" 
                    step="0.01"
                    class="h-12 rounded-xl bg-white dark:bg-slate-900 border-indigo-100 dark:border-indigo-800 font-bold"
                />
                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    Estimasi: Rp {{ new Intl.NumberFormat('id-ID').format(Number(form.amount) * Number(form.exchange_rate)) }}
                </p>
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
            <div class="space-y-2 relative">
                <div class="flex justify-between items-center">
                    <Label for="description">Deskripsi</Label>
                    <span v-if="isAutoAssigned" class="text-[10px] font-black uppercase text-indigo-500 flex items-center gap-1 animate-bounce">
                        <Sparkles class="w-3 h-3" /> Magic Category Selected
                    </span>
                </div>
                <Input id="description" v-model="form.description" placeholder="Makan malam, belanja, dll." class="h-12 rounded-xl" />
            </div>

            <Button type="submit" class="w-full h-14 rounded-2xl text-lg font-bold shadow-lg shadow-indigo-100 dark:shadow-none" :disabled="form.processing">
                Simpan Transaksi
            </Button>
        </form>
    </div>
</template>
