<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Bell, CheckCircle2, AlertCircle, Sparkles, X } from 'lucide-vue-next';

interface Notification {
    id: number;
    type: 'success' | 'error' | 'info' | 'achievement';
    message: string;
}

const notifications = ref<Notification[]>([]);
const nextId = ref(1);

const addNotification = (type: Notification['type'], message: string) => {
    const id = nextId.value++;
    notifications.value.push({ id, type, message });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeNotification(id);
    }, 5000);
};

const removeNotification = (id: number) => {
    notifications.value = notifications.value.filter(n => n.id !== id);
};

const handleNotifyEvent = (event: any) => {
    const { type, message } = event.detail;
    addNotification(type, message);
};

// Listen for flash messages from Inertia
onMounted(() => {
    const page = usePage();
    
    // Initial check for flash messages
    if (page.props.flash?.success) addNotification('success', page.props.flash.success);
    if (page.props.flash?.error) addNotification('error', page.props.flash.error);

    // Add global event listener for manual triggers
    window.addEventListener('notify', handleNotifyEvent);
});

onUnmounted(() => {
    window.removeEventListener('notify', handleNotifyEvent);
});
</script>

<template>
    <div class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none max-w-sm w-full">
        <TransitionGroup name="notification">
            <div 
                v-for="notif in notifications" 
                :key="notif.id"
                class="pointer-events-auto group relative overflow-hidden bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-2xl flex items-start gap-4 transition-all"
            >
                <!-- Indicator Bar -->
                <div 
                    class="absolute left-0 top-0 bottom-0 w-1"
                    :class="{
                        'bg-emerald-500': notif.type === 'success',
                        'bg-rose-500': notif.type === 'error',
                        'bg-indigo-500': notif.type === 'info',
                        'bg-amber-500': notif.type === 'achievement'
                    }"
                ></div>

                <!-- Icon -->
                <div 
                    class="p-2 rounded-xl shrink-0"
                    :class="{
                        'bg-emerald-100 text-emerald-600': notif.type === 'success',
                        'bg-rose-100 text-rose-600': notif.type === 'error',
                        'bg-indigo-100 text-indigo-600': notif.type === 'info',
                        'bg-amber-100 text-amber-600': notif.type === 'achievement'
                    }"
                >
                    <CheckCircle2 v-if="notif.type === 'success'" class="w-5 h-5" />
                    <AlertCircle v-else-if="notif.type === 'error'" class="w-5 h-5" />
                    <Sparkles v-else-if="notif.type === 'achievement'" class="w-5 h-5" />
                    <Bell v-else class="w-5 h-5" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-slate-900 dark:text-white mb-0.5">
                        {{ notif.type === 'achievement' ? 'Lencana Baru!' : 'Notifikasi' }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                        {{ notif.message }}
                    </p>
                </div>

                <!-- Close -->
                <button @click="removeNotification(notif.id)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.notification-enter-from {
    opacity: 0;
    transform: translateX(30px) scale(0.9);
}

.notification-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
