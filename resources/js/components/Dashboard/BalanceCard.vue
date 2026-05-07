<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    balance: number;
    currency: string;
    accountName: string;
    type: string;
    color: string;
}

const props = defineProps<Props>();

const formattedBalance = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: props.currency,
    }).format(props.balance);
});
</script>

<template>
    <div 
        class="relative overflow-hidden rounded-3xl p-6 text-white shadow-2xl transition-all hover:scale-[1.02]"
        :style="{ backgroundColor: color || '#1e293b' }"
    >
        <!-- Glassmorphism overlay -->
        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
        
        <div class="relative z-10 flex flex-col justify-between h-full">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-white/70">{{ type }}</p>
                    <h3 class="text-xl font-bold mt-1">{{ accountName }}</h3>
                </div>
                <div class="p-2 bg-white/20 rounded-xl">
                    <slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </slot>
                </div>
            </div>
            
            <div class="mt-8">
                <p class="text-sm text-white/60">Current Balance</p>
                <h2 class="text-3xl font-black mt-1 tracking-tight">{{ formattedBalance }}</h2>
            </div>
            
            <div class="mt-6 flex items-center gap-2">
                <span class="flex h-2 w-2 rounded-full bg-green-400 animate-pulse"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-white/50">Active Now</span>
            </div>
        </div>

        <!-- Decorative circles -->
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-black/10 blur-3xl"></div>
    </div>
</template>

<style scoped>
.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
</style>
