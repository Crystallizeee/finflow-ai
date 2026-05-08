<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Label } from '@/components/ui/label';
import { Fingerprint, Shield, Smartphone } from 'lucide-vue-next';

const isBiometricEnabled = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('finflow_biometric_enabled');
    isBiometricEnabled.value = saved === 'true';
});

const toggleBiometric = () => {
    isBiometricEnabled.value = !isBiometricEnabled.value;
    localStorage.setItem('finflow_biometric_enabled', isBiometricEnabled.value.toString());
};

const breadcrumbs = [
    { title: 'Security Settings', href: '/settings/security' }
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Security Settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall 
                    title="Security & Privacy" 
                    description="Kelola cara Anda mengamankan data finansial di perangkat ini." 
                />

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                        <div class="flex gap-4 items-center">
                            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl">
                                <Fingerprint class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <Label class="text-base font-bold text-slate-900 dark:text-white">Kunci Biometrik PWA</Label>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Gunakan FaceID atau Fingerprint saat membuka aplikasi.</p>
                            </div>
                        </div>
                        
                        <!-- Simple Toggle -->
                        <button 
                            @click="toggleBiometric"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                            :class="isBiometricEnabled ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-800'"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="isBiometricEnabled ? 'translate-x-5' : 'translate-x-0'"
                            />
                        </button>
                    </div>

                    <div class="p-4 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                        <div class="flex gap-3">
                            <Shield class="w-4 h-4 text-slate-400 mt-0.5" />
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Fitur ini menyimpan status verifikasi Anda secara lokal di perangkat ini. 
                                Sesi Anda akan otomatis terkunci kembali setelah 30 menit tidak aktif.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <HeadingSmall 
                        title="Device Trusted Status" 
                        description="Perangkat ini saat ini terdaftar sebagai perangkat utama Anda." 
                    />
                    <div class="mt-4 flex items-center gap-3 text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 p-3 rounded-xl border border-emerald-100 dark:border-emerald-900/30">
                        <Smartphone class="w-5 h-5" />
                        <span class="text-xs font-bold uppercase tracking-widest">Active & Secured</span>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
