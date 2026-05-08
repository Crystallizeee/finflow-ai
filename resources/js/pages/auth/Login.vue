<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk - FinFlow AI" />

    <div class="bg-[#f8f9fa] text-[#191c1d] min-h-screen flex items-center justify-center font-sans overflow-x-hidden">
        <!-- Load Material Symbols -->
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
        <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>

        <div class="flex flex-col lg:flex-row w-full min-h-screen">
            <!-- Left Side: Form -->
            <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-20 bg-white">
                <div class="max-w-md w-full mx-auto">
                    <div class="mb-10 text-center lg:text-left">
                        <Link href="/" class="flex items-center justify-center lg:justify-start gap-2 mb-6 group">
                            <span class="material-symbols-outlined text-black text-3xl transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
                            <span class="text-2xl font-black tracking-tighter text-black">FinFlow AI</span>
                        </Link>
                        <h1 class="text-3xl font-black text-[#191c1d] mb-2 font-['Hanken_Grotesk']">Selamat Datang Kembali</h1>
                        <p class="text-sm font-medium text-[#444748] font-['Inter']">Masuk untuk mengakses AI Financial Co-pilot Anda dan kelola keuangan dengan lebih cerdas.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-[#191c1d] mb-1 font-['Inter']" for="email">Email</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#747878]" style="font-size: 20px;">mail</span>
                                <input 
                                    v-model="form.email"
                                    type="email" 
                                    id="email" 
                                    class="pl-10 w-full bg-[#f8f9fa] text-[#191c1d] border-[#c4c7c7] focus:border-black focus:ring-black rounded-xl h-12 text-sm font-medium transition-all" 
                                    placeholder="nama@email.com" 
                                    required 
                                />
                            </div>
                            <div v-if="form.errors.email" class="text-xs text-red-600 mt-1 font-bold">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-bold text-[#191c1d] font-['Inter']" for="password">Kata Sandi</label>
                                <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-bold text-[#0058be] hover:underline">Lupa kata sandi?</Link>
                            </div>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#747878]" style="font-size: 20px;">lock</span>
                                <input 
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'" 
                                    id="password" 
                                    class="pl-10 pr-10 w-full bg-[#f8f9fa] text-[#191c1d] border-[#c4c7c7] focus:border-black focus:ring-black rounded-xl h-12 text-sm font-medium transition-all" 
                                    placeholder="••••••••" 
                                    required 
                                />
                                <button @click="showPassword = !showPassword" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#747878] hover:text-black transition-colors">
                                    <span class="material-symbols-outlined" style="font-size: 20px;">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                                </button>
                            </div>
                            <div v-if="form.errors.password" class="text-xs text-red-600 mt-1 font-bold">{{ form.errors.password }}</div>
                        </div>

                        <div class="flex items-center">
                            <input v-model="form.remember" type="checkbox" id="remember" class="h-4 w-4 text-black focus:ring-black border-[#c4c7c7] rounded bg-[#f8f9fa]" />
                            <label class="ml-2 block text-xs font-bold text-[#444748] font-['Inter']" for="remember">Ingat saya di perangkat ini</label>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full flex justify-center items-center gap-2 py-4 px-4 rounded-2xl bg-black text-white font-black hover:bg-[#1c1b1b] transition-all shadow-lg hover:shadow-xl active:scale-[0.98]"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Memproses...</span>
                            <template v-else>
                                Masuk Sekarang
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </template>
                        </button>
                    </form>

                    <div class="mt-8">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-[#e1e3e4]"></div>
                            </div>
                            <div class="relative flex justify-center text-xs">
                                <span class="px-2 bg-white text-[#747878] font-bold uppercase tracking-widest">Atau masuk dengan</span>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-3">
                            <button class="flex items-center justify-center gap-3 w-full py-3.5 px-4 border border-[#c4c7c7] rounded-2xl bg-white text-[#191c1d] font-bold hover:bg-[#f3f4f5] transition-all active:scale-[0.98]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                                </svg>
                                Akun Google
                            </button>
                        </div>
                    </div>

                    <p class="mt-10 text-center text-sm font-medium text-[#444748]">
                        Belum punya akun? <Link :href="route('register')" class="font-black text-[#0058be] hover:underline">Daftar sekarang</Link>
                    </p>
                </div>
            </div>

            <!-- Right Side: Decorative -->
            <div class="hidden lg:block lg:flex-1 relative overflow-hidden bg-[#edeeef]">
                <div 
                    class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-transform duration-[10s] hover:scale-110" 
                    style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBWNahEiTTlo8a9Dqe3dm_9iM82WcrXyr7iyHvJBJnDQs8j5UhyMFs-MeWlIijFblkpqo_30BSjjfBa79YlYM4mb0riJ44TvIVzoOhMBzi0LGhxu3y2Yl0On65sZLecnOR6C-dfRj6BAsm0o4uhZapwLqgWJWj7egn0LZ67Zl2maCxDRUw-XkoV9D0wup_RlpBmIn5xVTsM_Wauw9zk4hooRDG_KD1xORXsGvKseRKFDG-u_yE0ggHo6M2jgnztnWNUkKzvaoKVdK8C');"
                ></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-16 text-white">
                    <div class="mb-6 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-[10px] font-black uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        AI Co-pilot Active
                    </div>
                    <h2 class="text-5xl font-black text-white mb-6 leading-[1.1] font-['Hanken_Grotesk']">Financial Co-pilot<br/>Gen-Z Terpercaya.</h2>
                    <p class="text-lg font-medium text-white/80 max-w-md font-['Inter']">Analisis prediktif, wawasan pengeluaran real-time, dan strategi investasi cerdas, semuanya dalam satu platform terpadu.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-sans {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
</style>
