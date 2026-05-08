<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    Camera, Users, Plus, X, 
    ChevronRight, Loader2, Receipt, 
    UserPlus, CheckCircle2, User as UserIcon
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import axios from 'axios';

interface BillItem {
    id: string;
    name: string;
    total_price: number;
    assignedTo: string[]; // IDs of friends
}

interface Friend {
    id: string;
    name: string;
}

const step = ref(1); // 1: Upload, 2: Split
const isScanning = ref(false);
const friends = ref<Friend[]>([
    { id: 'me', name: 'Saya (Anda)' }
]);
const newFriendName = ref('');
const billItems = ref<BillItem[]>([]);
const totalAmount = ref(0);
const computedTotalAmount = computed(() => {
    return billItems.value.reduce((acc, item) => acc + (Number(item.total_price) || 0), 0);
});
const selectedFile = ref<File | null>(null);

const addFriend = () => {
    if (!newFriendName.value.trim()) return;
    friends.value.push({
        id: Math.random().toString(36).substr(2, 9),
        name: newFriendName.value
    });
    newFriendName.value = '';
};

const onFileChange = (e: any) => {
    selectedFile.value = e.target.files[0];
    scanReceipt();
};

const scanReceipt = async () => {
    if (!selectedFile.value) return;
    
    isScanning.value = true;
    const formData = new FormData();
    formData.append('image', selectedFile.value);

    try {
        const response = await axios.post(route('split-bill.scan'), formData);
        if (response.data.success) {
            billItems.value = response.data.items.map((item: any) => ({
                ...item,
                assignedTo: ['me'] // Default to me
            }));
            totalAmount.value = response.data.total;
            step.value = 2;
        }
    } catch (err: any) {
        const msg = err.response?.data?.error || 'Gagal memindai struk. Pastikan koneksi internet stabil.';
        alert(msg);
    } finally {
        isScanning.value = false;
    }
};

const toggleAssignment = (itemId: string, friendId: string) => {
    const item = billItems.value.find(i => i.id === itemId);
    if (!item) return;

    const index = item.assignedTo.indexOf(friendId);
    if (index > -1) {
        if (item.assignedTo.length > 1) {
            item.assignedTo.splice(index, 1);
        }
    } else {
        item.assignedTo.push(friendId);
    }
};

const getFriendTotal = (friendId: string) => {
    return billItems.value.reduce((acc, item) => {
        if (item.assignedTo.includes(friendId)) {
            return acc + (item.total_price / item.assignedTo.length);
        }
        return acc;
    }, 0);
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Split Bill', href: '/split-bill' }
];
</script>

<template>
    <Head title="Smart Split Bill" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-10 max-w-5xl mx-auto w-full">
            
            <!-- Step 1: Upload & Friends Setup -->
            <div v-if="step === 1" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="text-center max-w-2xl mx-auto">
                    <div class="w-20 h-20 bg-indigo-100 text-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-indigo-100">
                        <Camera class="w-10 h-10" />
                    </div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white mb-4">Smart Split Bill</h1>
                    <p class="text-slate-500 font-medium text-lg leading-relaxed">Foto struk belanjamu, biarkan AI membagi tagihannya secara adil dan cepat.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Upload Box -->
                    <div class="bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[2.5rem] p-10 text-center hover:border-indigo-400 transition-all group relative cursor-pointer overflow-hidden">
                        <input type="file" @change="onFileChange" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept="image/*" />
                        <div v-if="!isScanning" class="space-y-4">
                            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                                <Plus class="w-8 h-8 text-slate-400 group-hover:text-indigo-600" />
                            </div>
                            <div>
                                <p class="font-black text-slate-900 dark:text-white">Klik untuk Upload Struk</p>
                                <p class="text-sm text-slate-400 font-medium mt-1">PNG, JPG up to 10MB</p>
                            </div>
                        </div>
                        <div v-else class="space-y-4">
                            <Loader2 class="w-12 h-12 text-indigo-600 animate-spin mx-auto" />
                            <p class="font-black text-indigo-600 animate-pulse">AI sedang membaca strukmu...</p>
                        </div>
                    </div>

                    <!-- Friends List -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[2.5rem] p-8 shadow-xl">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-2">
                            <Users class="w-6 h-6 text-indigo-600" />
                            Siapa saja yang ikut?
                        </h3>
                        
                        <div class="space-y-4 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="friend in friends" :key="friend.id" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black">
                                        {{ friend.name.charAt(0) }}
                                    </div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ friend.name }}</span>
                                </div>
                                <button v-if="friend.id !== 'me'" @click="friends = friends.filter(f => f.id !== friend.id)" class="text-slate-400 hover:text-rose-500 transition-colors">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <UserPlus class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <input 
                                    v-model="newFriendName" 
                                    @keyup.enter="addFriend"
                                    type="text" 
                                    placeholder="Tambah nama teman..." 
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-indigo-500 transition-all font-bold"
                                />
                            </div>
                            <button @click="addFriend" class="bg-indigo-600 text-white p-3 rounded-xl hover:bg-indigo-700 transition-all">
                                <Plus class="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Split Items -->
            <div v-if="step === 2" class="space-y-8 animate-in fade-in zoom-in-95 duration-500">
                <div class="flex justify-between items-center bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white">Bagi Tagihan</h2>
                        <p class="text-slate-500 font-medium">Klik nama teman di tiap item untuk membagi biaya.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Total Struk</p>
                        <p class="text-3xl font-black text-indigo-600">{{ formatCurrency(computedTotalAmount) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Items List -->
                    <div class="lg:col-span-2 space-y-4">
                        <div v-for="item in billItems" :key="item.id" class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center justify-center">
                                        <Receipt class="w-6 h-6 text-slate-400" />
                                    </div>
                                    <div class="flex-1 min-w-0 pr-4">
                                        <input 
                                            v-model="item.name" 
                                            type="text" 
                                            class="w-full bg-transparent border-b border-transparent hover:border-slate-200 dark:hover:border-slate-700 focus:border-indigo-500 focus:ring-0 p-0 pb-1 mb-1 font-black text-slate-900 dark:text-white transition-colors"
                                            placeholder="Nama Barang..."
                                        />
                                        <div class="flex items-center gap-1">
                                            <span class="text-indigo-600 font-bold text-sm">Rp</span>
                                            <input 
                                                v-model.number="item.total_price" 
                                                type="number" 
                                                class="w-full bg-transparent border-b border-transparent hover:border-indigo-200 dark:hover:border-indigo-900 focus:border-indigo-500 focus:ring-0 p-0 text-indigo-600 font-bold transition-colors"
                                                placeholder="0"
                                            />
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="friend in friends" 
                                    :key="friend.id"
                                    @click="toggleAssignment(item.id, friend.id)"
                                    class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 border-2"
                                    :class="item.assignedTo.includes(friend.id) 
                                        ? 'bg-indigo-600 border-indigo-600 text-white' 
                                        : 'bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-800 text-slate-400'"
                                >
                                    {{ friend.name }}
                                    <CheckCircle2 v-if="item.assignedTo.includes(friend.id)" class="w-3 h-3" />
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-3 font-bold uppercase tracking-tighter" v-if="item.assignedTo.length > 1">
                                Per orang: {{ formatCurrency(item.total_price / item.assignedTo.length) }}
                            </p>
                        </div>
                    </div>

                    <!-- Summary Per Friend -->
                    <div class="space-y-6">
                        <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-2xl">
                            <h3 class="text-xl font-black mb-6 flex items-center gap-2">
                                <CheckCircle2 class="w-6 h-6" />
                                Ringkasan
                            </h3>
                            <div class="space-y-4">
                                <div v-for="friend in friends" :key="friend.id" class="flex justify-between items-center py-3 border-b border-white/10 last:border-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-xs font-black">
                                            {{ friend.name.charAt(0) }}
                                        </div>
                                        <span class="font-bold text-sm">{{ friend.name }}</span>
                                    </div>
                                    <span class="font-black">{{ formatCurrency(getFriendTotal(friend.id)) }}</span>
                                </div>
                            </div>
                            
                            <button 
                                @click="router.post(route('split-bill.store'))"
                                class="w-full bg-white text-indigo-600 py-4 rounded-2xl font-black mt-8 hover:bg-indigo-50 transition-all shadow-xl shadow-indigo-900/20"
                            >
                                Konfirmasi & Simpan
                            </button>
                        </div>
                        
                        <button @click="step = 1" class="w-full text-slate-400 font-bold hover:text-indigo-600 transition-colors flex items-center justify-center gap-2">
                            <Loader2 v-if="isScanning" class="w-4 h-4 animate-spin" />
                            Ulangi Scan Struk
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e293b;
}
</style>
