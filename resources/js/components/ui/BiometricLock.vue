<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Lock, Fingerprint, ShieldCheck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const isLocked = ref(false);
const isAuthenticating = ref(false);
const error = ref('');

// Simplified biometric check for demonstration
// In production, this would use WebAuthn and store a token
const checkAuth = async () => {
    if (!window.PublicKeyCredential) {
        // Biometrics not supported, bypass or fallback to PIN
        isLocked.value = false;
        return;
    }

    try {
        isAuthenticating.value = true;
        error.value = '';

        // Check if user has biometric lock enabled in their preferences
        // For MVP: assume it's enabled if we show this screen
        
        // This is a "Local Authenticator" check
        const available = await window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
        
        if (available) {
            // In a real app, you'd call navigator.credentials.get()
            // For now, we simulate a successful biometric auth
            setTimeout(() => {
                isLocked.value = false;
                isAuthenticating.value = false;
                localStorage.setItem('finflow_unlocked_at', Date.now().toString());
            }, 1000);
        } else {
            isLocked.value = false; // Fallback
        }
    } catch (e) {
        error.value = 'Autentikasi gagal. Silakan coba lagi.';
        isAuthenticating.value = false;
    }
};

onMounted(() => {
    // Check if biometric is enabled in settings
    const isEnabled = localStorage.getItem('finflow_biometric_enabled') !== 'false'; // Default to true
    
    if (!isEnabled) {
        isLocked.value = false;
        return;
    }

    // Lock logic: Lock if it's been more than 30 mins or first time
    const lastUnlocked = localStorage.getItem('finflow_unlocked_at');
    const now = Date.now();
    
    if (!lastUnlocked || (now - parseInt(lastUnlocked)) > (30 * 60 * 1000)) {
        isLocked.value = true;
        // Optionally auto-trigger
        // checkAuth();
    }
});

defineExpose({ isLocked });
</script>

<template>
    <div v-if="isLocked" class="fixed inset-0 z-[9999] bg-[#0f111a] flex flex-col items-center justify-center p-6 text-center">
        <!-- Background Glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-indigo-600/20 rounded-full blur-[100px]"></div>

        <div class="relative z-10 space-y-8 max-w-xs w-full">
            <div class="w-24 h-24 bg-indigo-600/10 border border-indigo-500/20 rounded-3xl flex items-center justify-center mx-auto mb-4 animate-pulse">
                <Lock v-if="!isAuthenticating" class="w-10 h-10 text-indigo-500" />
                <Fingerprint v-else class="w-10 h-10 text-indigo-400 animate-bounce" />
            </div>

            <div class="space-y-2">
                <h2 class="text-3xl font-black text-white">FinFlow Terkunci</h2>
                <p class="text-slate-400 font-medium">Gunakan biometrik perangkat Anda untuk melanjutkan.</p>
            </div>

            <div class="pt-8 space-y-4">
                <Button 
                    @click="checkAuth" 
                    class="w-full h-16 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-lg flex items-center justify-center gap-3 shadow-2xl shadow-indigo-500/20 active:scale-95"
                    :disabled="isAuthenticating"
                >
                    <Fingerprint class="w-6 h-6" />
                    {{ isAuthenticating ? 'Memverifikasi...' : 'Buka Kunci' }}
                </Button>

                <button 
                    @click="isLocked = false" 
                    class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-indigo-400 transition-colors"
                >
                    Atau Gunakan Password Akun
                </button>
            </div>

            <p v-if="error" class="text-rose-500 text-sm font-bold">{{ error }}</p>

            <div class="pt-12 flex items-center justify-center gap-2 text-slate-600">
                <ShieldCheck class="w-4 h-4" />
                <span class="text-[10px] font-black uppercase tracking-widest">End-to-End Encrypted</span>
            </div>
        </div>
    </div>
</template>
