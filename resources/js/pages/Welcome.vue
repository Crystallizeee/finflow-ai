<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Zap, 
    ArrowRight, 
    ShieldCheck, 
    BarChart3, 
    Smartphone, 
    Globe,
    LayoutDashboard,
    PieChart,
    Wallet
} from 'lucide-vue-next';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="FinFlow AI - Your Money on Autopilot" />

    <div class="min-h-screen bg-[#0f111a] text-white font-sans selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Background Effects -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-indigo-600/20 rounded-full blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-cyan-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 border-b border-white/5 bg-[#0f111a]/80 backdrop-blur-xl">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2 group">
                    <div class="p-2 bg-indigo-600 rounded-xl group-hover:scale-110 transition-transform">
                        <Wallet class="w-6 h-6 text-white" />
                    </div>
                    <span class="text-2xl font-black tracking-tighter">FinFlow AI</span>
                </Link>

                <div class="hidden md:flex items-center gap-10">
                    <a href="#features" class="text-sm font-bold text-slate-400 hover:text-white transition-colors">Features</a>
                    <a href="#advisor" class="text-sm font-bold text-slate-400 hover:text-white transition-colors">AI Advisor</a>
                    <a href="#security" class="text-sm font-bold text-slate-400 hover:text-white transition-colors">Security</a>
                </div>

                <div class="flex items-center gap-4">
                    <Link 
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-6 py-2.5 rounded-xl bg-white text-black font-black text-sm hover:bg-slate-100 transition-all shadow-lg"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-black text-slate-400 hover:text-white transition-colors px-4">Login</Link>
                        <Link 
                            :href="route('register')"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20"
                        >
                            Sign Up
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <main class="relative z-10 pt-32">
            <!-- Hero Section -->
            <section class="max-w-7xl mx-auto px-6 py-20 grid lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-300">New: Receipt Scanning AI</span>
                    </div>
                    
                    <h1 class="text-6xl md:text-8xl font-black leading-[1] tracking-tight">
                        Your Money on <br/>
                        <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent">Autopilot.</span>
                    </h1>
                    
                    <p class="text-xl text-slate-400 font-medium max-w-lg leading-relaxed">
                        Meet your AI Financial Co-pilot. Budget, save, and invest with a vibe. Built for the next generation of wealth.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <Link 
                            :href="route('register')"
                            class="px-10 py-5 rounded-2xl bg-indigo-600 text-white font-black text-lg hover:bg-indigo-700 transition-all flex items-center justify-center gap-3 shadow-2xl shadow-indigo-500/40 active:scale-95"
                        >
                            Level Up Your Wallet
                            <ArrowRight class="w-6 h-6" />
                        </Link>
                    </div>

                    <div class="flex items-center gap-4 pt-8">
                        <div class="flex -space-x-3">
                            <div v-for="i in 4" :key="i" class="w-10 h-10 rounded-full border-2 border-[#0f111a] bg-slate-800 flex items-center justify-center overflow-hidden">
                                <img :src="`https://i.pravatar.cc/150?u=${i}`" alt="User" />
                            </div>
                        </div>
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Join 100k+ leveling up.</p>
                    </div>
                </div>

                <div class="relative group">
                    <div class="absolute inset-0 bg-indigo-500/20 rounded-full blur-[100px] group-hover:bg-indigo-500/30 transition-colors"></div>
                    <img 
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuBM_BiDJE_fW8s30e66NkNRKUBalezzLbx2aGpFPK9fRVvhdSpCbOJwY_5KQ_CEt944i8EJXc4OiieO2G7UHsIv59-zAk8hBAHScsnWGQknAbFM0JR6AW35T-gld3331SGn1Ako1VXtLT1mc41JHZz0KC_pBvg8gx34_lfm828nG9FqdmaIXuLQllrWGlFn9bbbgFIoszsbdM_Nx5_H6_-vD88fP5bxzAXJ02vACE6Vm7fQ86agI_SJhobzzvn_w_KFqlSQfPFGzbrh" 
                        alt="FinFlow AI Core" 
                        class="relative z-10 w-full h-auto drop-shadow-[0_0_50px_rgba(79,70,229,0.4)] animate-float"
                    />
                </div>
            </section>

            <!-- Features Grid -->
            <section id="features" class="max-w-7xl mx-auto px-6 py-32">
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1: Smart Budget -->
                    <div class="md:col-span-2 group relative p-10 rounded-[3rem] bg-white/5 border border-white/10 hover:bg-white/[0.08] transition-all overflow-hidden">
                        <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 flex items-center justify-center mb-8">
                                <LayoutDashboard class="w-8 h-8 text-indigo-400" />
                            </div>
                            <h3 class="text-4xl font-black mb-4">Smart Budgets</h3>
                            <p class="text-xl text-slate-400 font-medium max-w-sm">Let AI find the flex. Auto-budgeting that actually works for your lifestyle.</p>
                            
                            <div class="mt-12 flex gap-3 opacity-50 h-20 items-end">
                                <div v-for="h in [40, 70, 50, 90, 60, 30]" :key="h" :style="{ height: h + '%' }" class="flex-1 bg-indigo-500/40 rounded-t-lg"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2: AI Advisor -->
                    <div id="advisor" class="group relative p-10 rounded-[3rem] bg-gradient-to-br from-indigo-600 to-purple-700 text-white shadow-2xl shadow-indigo-500/20 overflow-hidden">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div>
                                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center mb-8">
                                    <Zap class="w-8 h-8 text-white" />
                                </div>
                                <h3 class="text-4xl font-black mb-4">AI Advisor</h3>
                                <p class="text-lg text-white/80 font-bold">Chat with your wealth coach 24/7. No cap, just gains.</p>
                            </div>
                            <div class="mt-12 p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                <p class="text-sm italic">"Based on your vibe check, you can save $50 this week by skipping delivery."</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3: Security -->
                    <div id="security" class="group relative p-10 rounded-[3rem] bg-white/5 border border-white/10 hover:bg-white/[0.08] transition-all overflow-hidden md:col-span-3">
                        <div class="flex flex-col md:flex-row gap-12 items-center">
                            <div class="flex-1">
                                <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 flex items-center justify-center mb-8">
                                    <ShieldCheck class="w-8 h-8 text-cyan-400" />
                                </div>
                                <h3 class="text-4xl font-black mb-4">Bank-Grade Security</h3>
                                <p class="text-xl text-slate-400 font-medium">Your data is encrypted and secure. We focus on privacy so you can focus on building wealth.</p>
                            </div>
                            <div class="flex gap-6">
                                <div v-for="icon in [PieChart, BarChart3, Smartphone, Globe]" :key="icon" class="w-20 h-20 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <component :is="icon" class="w-8 h-8 text-slate-500" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-white/5 py-20">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-10">
                <div class="flex items-center gap-2">
                    <Wallet class="w-6 h-6 text-indigo-500" />
                    <span class="text-2xl font-black tracking-tighter">FinFlow AI</span>
                </div>
                
                <div class="flex flex-wrap justify-center gap-8 text-xs font-black uppercase tracking-widest text-slate-500">
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Discord</a>
                    <a href="#" class="hover:text-white transition-colors">Twitter</a>
                </div>

                <p class="text-xs font-bold text-slate-600">© 2024 FinFlow AI. For the next generation.</p>
            </div>
        </footer>
    </div>
</template>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}
.animate-float {
    animation: float 6s ease-in-out infinite;
}
</style>
