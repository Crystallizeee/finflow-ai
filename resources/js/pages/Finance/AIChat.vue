<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import { Send, Loader2, Bot, User, Trash2 } from 'lucide-vue-next';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AI Assistant', href: '/ai-chat' },
];

interface Message {
    role: 'user' | 'assistant';
    content: string;
}

const messages = ref<Message[]>([]);
const form = useForm({
    content: '',
});
const isLoading = ref(false);
const chatContainer = ref<HTMLElement | null>(null);

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

onMounted(() => {
    // Initial greeting
    messages.value.push({
        role: 'assistant',
        content: 'Halo! Saya FinFlow AI. Ada yang bisa saya bantu terkait analisis pengeluaran, sisa budget, atau saran keuangan hari ini?'
    });
});

const renderMarkdown = (text: string) => {
    const rawMarkup = marked.parse(text) as string;
    return DOMPurify.sanitize(rawMarkup);
};

const sendMessage = async () => {
    if (!form.content.trim() || isLoading.value) return;

    const userMessage = form.content;
    messages.value.push({ role: 'user', content: userMessage });
    form.content = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        // Send to backend API using axios
        const response = await axios.post(route('ai-chat.send'), {
            messages: messages.value.map(m => ({ role: m.role, content: m.content }))
        });

        const data = response.data;

        if (data.success) {
            messages.value.push({ role: 'assistant', content: data.message });
        } else {
            messages.value.push({ role: 'assistant', content: 'Maaf, terjadi kesalahan: ' + (data.error || 'Server error.') });
        }
    } catch (err: any) {
        let errorMsg = 'Maaf, gagal terhubung ke server. Silakan coba lagi.';
        if (err.response && err.response.data && err.response.data.error) {
            errorMsg = err.response.data.error;
        }
        messages.value.push({ role: 'assistant', content: errorMsg });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};

const clearChat = () => {
    messages.value = [
        {
            role: 'assistant',
            content: 'Percakapan dibersihkan. Ada yang bisa saya bantu?'
        }
    ];
};
</script>

<template>
    <Head title="AI Assistant" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col max-w-4xl mx-auto w-full px-4 py-6 md:p-6">
            
            <!-- Header -->
            <header class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-black tracking-tight flex items-center gap-2">
                        <Bot class="h-8 w-8 text-indigo-600" />
                        FinFlow AI
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm md:text-base">Asisten keuangan cerdas yang paham kondisi dompetmu.</p>
                </div>
                <button @click="clearChat" class="text-slate-400 hover:text-red-500 transition-colors p-2" title="Bersihkan Percakapan">
                    <Trash2 class="h-5 w-5" />
                </button>
            </header>

            <!-- Chat Area -->
            <div class="flex-1 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden min-h-[500px] h-[calc(100vh-250px)]">
                
                <!-- Messages -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                    <div v-for="(msg, idx) in messages" :key="idx" 
                        class="flex gap-4 max-w-[85%]" 
                        :class="msg.role === 'user' ? 'ml-auto flex-row-reverse' : ''">
                        
                        <!-- Avatar -->
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mt-1"
                            :class="msg.role === 'user' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50' : 'bg-green-100 text-green-600 dark:bg-green-900/50'">
                            <User v-if="msg.role === 'user'" class="h-4 w-4" />
                            <Bot v-else class="h-4 w-4" />
                        </div>

                        <!-- Bubble -->
                        <div class="px-5 py-3.5 rounded-2xl text-sm leading-relaxed"
                            :class="msg.role === 'user' 
                                ? 'bg-indigo-600 text-white rounded-tr-none' 
                                : 'bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-100 dark:border-slate-700 rounded-tl-none'">
                            
                            <div v-if="msg.role === 'user'" class="whitespace-pre-wrap">{{ msg.content }}</div>
                            <div v-else class="prose prose-sm dark:prose-invert prose-p:my-1 prose-ul:my-1 max-w-none" v-html="renderMarkdown(msg.content)"></div>
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div v-if="isLoading" class="flex gap-4 max-w-[85%]">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mt-1">
                            <Bot class="h-4 w-4" />
                        </div>
                        <div class="px-5 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-tl-none flex items-center gap-2">
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
                    <form @submit.prevent="sendMessage" class="relative flex items-center">
                        <input 
                            v-model="form.content" 
                            type="text" 
                            placeholder="Tanya tentang keuanganmu..." 
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl pl-5 pr-14 py-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner"
                            :disabled="isLoading"
                        />
                        <button 
                            type="submit" 
                            :disabled="isLoading || !form.content.trim()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <Loader2 v-if="isLoading" class="h-5 w-5 animate-spin" />
                            <Send v-else class="h-4 w-4 ml-0.5" />
                        </button>
                    </form>
                    <p class="text-center text-[10px] text-slate-400 mt-2">FinFlow AI dapat membuat kesalahan. Harap periksa kembali saran keuangan.</p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
