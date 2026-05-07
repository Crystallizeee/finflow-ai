<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Camera, Loader2, CheckCircle2, X, ArrowRight, Wallet, Tag, Plus, Trash2, Pencil } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Scan Struk', href: '/receipts' },
];

const props = defineProps<{
    receipts: any;
    accounts: { id: string; name: string; balance: number; currency: string }[];
    categories: { id: string; name: string; type: string }[];
}>();

const form = useForm({ receipt: null as File | null });
const previewUrl = ref<string | null>(null);

// Confirmation modal state
const showModal = ref(false);
const selectedReceipt = ref<any>(null);

interface EditableItem {
    name: string;
    category_id: string | null;
    price_per_unit: number;
    quantity: number;
    discount: number;
    total_price: number;
}

const editableItems = ref<EditableItem[]>([]);

const confirmForm = useForm({
    account_id: '',
    category_id: '',
    type: 'expense' as 'expense' | 'income',
    amount: 0,
    description: '',
    date: '',
});

// Recalculate item total_price when its fields change
const recalcItem = (item: EditableItem) => {
    item.total_price = Math.max(0, (item.price_per_unit * item.quantity) - item.discount);
};

// Recalculate total from all items
const recalcTotal = () => {
    if (editableItems.value.length > 0) {
        const sum = editableItems.value.reduce((acc, i) => acc + i.total_price, 0);
        confirmForm.amount = Math.round(sum * 100) / 100;
    }
};

const addItem = () => {
    editableItems.value.push({ name: '', category_id: null, price_per_unit: 0, quantity: 1, discount: 0, total_price: 0 });
};

const removeItem = (idx: number) => {
    editableItems.value.splice(idx, 1);
    recalcTotal();
};

const onItemChange = (item: EditableItem) => {
    recalcItem(item);
    recalcTotal();
};

const onFileChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.receipt = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('receipts.store'), {
        onSuccess: () => { form.reset(); previewUrl.value = null; },
    });
};

const openConfirmModal = (receipt: any) => {
    selectedReceipt.value = receipt;
    const data = receipt.extracted_data ?? {};
    confirmForm.type = data.type ?? 'expense';
    confirmForm.amount = data.total_amount ?? 0;
    confirmForm.description = (data.type === 'income' ? 'Transfer Masuk: ' : 'Scan Struk: ') + (receipt.merchant_name ?? 'Tidak Diketahui');
    confirmForm.date = data.date ?? new Date().toISOString().split('T')[0];
    confirmForm.account_id = props.accounts[0]?.id ?? '';
    const keyword = data.type === 'income' ? 'pendapatan' : 'belanja';
    const matchedCat = props.categories.find(c => c.name.toLowerCase().includes(keyword));
    confirmForm.category_id = matchedCat?.id ?? props.categories[0]?.id ?? '';

    // Load editable items from receipt data or receipt_items relation
    const srcItems = (receipt.items?.length ? receipt.items : data.items) ?? [];
    editableItems.value = srcItems.map((i: any) => ({
        name: i.name ?? '',
        category_id: i.category_id ?? null,
        price_per_unit: Number(i.price_per_unit ?? i.price ?? 0),
        quantity: Number(i.quantity ?? 1),
        discount: Number(i.discount ?? 0),
        total_price: Number(i.total_price ?? 0),
    }));

    showModal.value = true;
};

const submitConfirm = () => {
    // Sync the (possibly edited) total from items
    recalcTotal();
    confirmForm.transform((data) => ({
        ...data,
        items: editableItems.value,
    })).post(route('receipts.confirm', selectedReceipt.value.id), {
        onSuccess: () => { showModal.value = false; },
    });
};

const fmt = (val: number, currency = 'IDR') =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency }).format(val ?? 0);
</script>

<template>
    <Head title="Scan Struk AI" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 max-w-5xl mx-auto w-full">
            <header class="text-center">
                <h1 class="text-4xl font-black tracking-tight">Scan Struk AI</h1>
                <p class="text-slate-500 mt-2 text-lg">Upload foto struk — AI baca, kamu koreksi, lalu konfirmasi.</p>
            </header>

            <!-- Upload Area -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800 p-8 text-center transition-all hover:border-indigo-500 shadow-sm">
                <form @submit.prevent="submit" class="flex flex-col items-center">
                    <div v-if="!previewUrl" class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center mb-4">
                        <Camera class="h-10 w-10 text-indigo-600" />
                    </div>
                    <div v-else class="mb-6 relative group">
                        <img :src="previewUrl" class="max-h-64 rounded-2xl shadow-xl border-4 border-white dark:border-slate-800" />
                        <button type="button" @click="previewUrl = null; form.receipt = null"
                            class="absolute -top-2 -right-2 bg-red-500 text-white p-1 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                    <label class="cursor-pointer">
                        <span v-if="!previewUrl" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-indigo-100">
                            Pilih Foto Struk
                        </span>
                        <input type="file" class="hidden" accept="image/*" @change="onFileChange" />
                    </label>
                    <button v-if="previewUrl" type="submit" :disabled="form.processing"
                        class="mt-4 px-10 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-green-100">
                        <Loader2 v-if="form.processing" class="h-5 w-5 animate-spin" />
                        {{ form.processing ? 'Sedang Memproses...' : 'Scan Sekarang' }}
                    </button>
                </form>
            </div>

            <!-- History -->
            <section>
                <h2 class="text-2xl font-bold mb-6">Hasil Scan Terbaru</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="receipt in receipts.data" :key="receipt.id"
                        class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col shadow-sm gap-4">

                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden flex-shrink-0">
                                <img :src="'/storage/' + receipt.storage_path" class="w-full h-full object-cover" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <h3 class="font-bold text-slate-900 dark:text-white truncate">
                                        {{ receipt.merchant_name || 'Memproses...' }}
                                    </h3>
                                    <span class="text-xs font-bold px-2 py-1 rounded-lg whitespace-nowrap" :class="{
                                        'bg-green-100 text-green-600': receipt.status === 'completed',
                                        'bg-amber-100 text-amber-600': receipt.status === 'processing',
                                        'bg-red-100 text-red-600': receipt.status === 'failed',
                                    }">{{ receipt.status }}</span>
                                </div>
                                <p class="text-sm font-semibold mt-1" :class="receipt.extracted_data?.type === 'income' ? 'text-green-500' : 'text-slate-700 dark:text-slate-300'">
                                    {{ receipt.total_amount ? fmt(receipt.total_amount, receipt.currency) : 'Menghitung...' }}
                                </p>
                                <p class="text-[10px] text-slate-400 mt-0.5 uppercase font-bold tracking-wider">
                                    {{ new Date(receipt.created_at).toLocaleString('id-ID') }}
                                </p>
                            </div>
                        </div>

                        <!-- Compact item preview -->
                        <div v-if="receipt.extracted_data?.items?.length" class="border-t border-slate-100 dark:border-slate-800 pt-3">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ receipt.extracted_data.items.length }} Item Terdeteksi</p>
                            <div class="space-y-0.5">
                                <div v-for="(item, idx) in receipt.extracted_data.items.slice(0,3)" :key="idx" class="flex justify-between text-xs text-slate-500">
                                    <span class="truncate pr-2">{{ item.name }}</span>
                                    <span class="whitespace-nowrap">{{ fmt(item.total_price || (item.price_per_unit * item.quantity), receipt.currency) }}</span>
                                </div>
                                <p v-if="receipt.extracted_data.items.length > 3" class="text-xs text-slate-400 italic">
                                    +{{ receipt.extracted_data.items.length - 3 }} item lainnya...
                                </p>
                            </div>
                        </div>

                        <!-- Action -->
                        <div v-if="receipt.status === 'completed' && !receipt.transaction_id" class="pt-1">
                            <button @click="openConfirmModal(receipt)"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all">
                                <Pencil class="h-4 w-4" />
                                Review & Simpan Transaksi
                            </button>
                        </div>
                        <div v-else-if="receipt.transaction_id" class="pt-1 flex items-center gap-2 text-green-500 text-sm font-medium">
                            <CheckCircle2 class="h-4 w-4" />
                            Sudah masuk ke transaksi
                        </div>
                    </div>

                    <div v-if="receipts.data.length === 0" class="col-span-full py-12 text-center text-slate-400">
                        Belum ada struk yang di-scan.
                    </div>
                </div>
            </section>
        </div>

        <!-- Confirm Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-2xl my-4 flex flex-col gap-0 overflow-hidden">
                    
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between p-6 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h2 class="text-xl font-black">Review & Konfirmasi Transaksi</h2>
                            <p class="text-slate-500 text-sm mt-1">{{ selectedReceipt?.merchant_name }} — Koreksi data jika AI salah baca</p>
                        </div>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 transition-colors mt-0.5">
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="p-6 flex flex-col gap-5 overflow-y-auto max-h-[75vh]">

                        <!-- ═══ ITEMS EDITOR ═══ -->
                        <div v-if="editableItems.length > 0 || true">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Detail Item <span class="text-xs text-indigo-500">(editable)</span></h3>
                                <button type="button" @click="addItem"
                                    class="flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                    <Plus class="h-3.5 w-3.5" /> Tambah Item
                                </button>
                            </div>

                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <!-- Table header -->
                                <div class="grid grid-cols-12 gap-1 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <div class="col-span-3">Nama Item</div>
                                    <div class="col-span-2">Kategori</div>
                                    <div class="col-span-2 text-right">Harga Sat.</div>
                                    <div class="col-span-1 text-center">Qty</div>
                                    <div class="col-span-1 text-right">Diskon</div>
                                    <div class="col-span-2 text-right">Total</div>
                                    <div class="col-span-1"></div>
                                </div>

                                <!-- Item rows -->
                                <div v-if="editableItems.length === 0" class="px-3 py-4 text-center text-sm text-slate-400 italic">
                                    Tidak ada item. Klik "+ Tambah Item" untuk menambahkan manual.
                                </div>
                                <div v-for="(item, idx) in editableItems" :key="idx"
                                    class="grid grid-cols-12 gap-1 px-3 py-2 border-t border-slate-100 dark:border-slate-800 items-center">
                                    <div class="col-span-3">
                                        <input v-model="item.name" type="text" placeholder="Nama barang"
                                            class="w-full text-xs px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>
                                    <div class="col-span-2">
                                        <select v-model="item.category_id"
                                            class="w-full text-[10px] px-1 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option :value="null">Kategori...</option>
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input v-model.number="item.price_per_unit" type="number" min="0" step="100"
                                            @input="onItemChange(item)"
                                            class="w-full text-xs px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-right" />
                                    </div>
                                    <div class="col-span-1">
                                        <input v-model.number="item.quantity" type="number" min="0.01" step="1"
                                            @input="onItemChange(item)"
                                            class="w-full text-xs px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-center" />
                                    </div>
                                    <div class="col-span-1">
                                        <input v-model.number="item.discount" type="number" min="0" step="100"
                                            @input="onItemChange(item)"
                                            class="w-full text-xs px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-right" />
                                    </div>
                                    <div class="col-span-2 text-right">
                                        <span class="text-xs font-semibold">{{ fmt(item.total_price, selectedReceipt?.currency) }}</span>
                                    </div>
                                    <div class="col-span-1 flex justify-end">
                                        <button type="button" @click="removeItem(idx)" class="text-red-400 hover:text-red-600 transition-colors">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Items subtotal -->
                                <div v-if="editableItems.length > 0" class="flex justify-between px-3 py-2.5 bg-indigo-50 dark:bg-indigo-900/20 border-t border-indigo-100 dark:border-indigo-800">
                                    <span class="text-xs font-bold text-indigo-600">Total dari Item</span>
                                    <span class="text-sm font-black text-indigo-700">{{ fmt(editableItems.reduce((s,i) => s + i.total_price, 0), selectedReceipt?.currency) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ TRANSACTION FIELDS ═══ -->
                        <form @submit.prevent="submitConfirm" class="flex flex-col gap-4">
                            <!-- Type toggle -->
                            <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                <button type="button" @click="confirmForm.type = 'expense'"
                                    :class="confirmForm.type === 'expense' ? 'bg-red-500 text-white' : 'bg-transparent text-slate-500'"
                                    class="flex-1 py-2 text-sm font-bold transition-all">
                                    Pengeluaran
                                </button>
                                <button type="button" @click="confirmForm.type = 'income'"
                                    :class="confirmForm.type === 'income' ? 'bg-green-500 text-white' : 'bg-transparent text-slate-500'"
                                    class="flex-1 py-2 text-sm font-bold transition-all">
                                    Pemasukan
                                </button>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Jumlah Total</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">Rp</span>
                                    <input v-model.number="confirmForm.amount" type="number" step="0.01"
                                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-bold text-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Account -->
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><Wallet class="h-3 w-3"/>Akun</label>
                                    <select v-model="confirmForm.account_id"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                            {{ acc.name }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Category -->
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><Tag class="h-3 w-3"/>Kategori</label>
                                    <select v-model="confirmForm.category_id"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Description -->
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Deskripsi</label>
                                    <input v-model="confirmForm.description" type="text"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" />
                                </div>
                                <!-- Date -->
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Tanggal</label>
                                    <input v-model="confirmForm.date" type="date"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" />
                                </div>
                            </div>

                            <button type="submit" :disabled="confirmForm.processing"
                                class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all mt-1 text-base">
                                <Loader2 v-if="confirmForm.processing" class="h-5 w-5 animate-spin" />
                                {{ confirmForm.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
