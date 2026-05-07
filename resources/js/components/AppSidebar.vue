<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Wallet, Receipt, Camera, BookOpen, Folder, Bot, PieChart, RefreshCcw, Award } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'AI Assistant',
        href: route('ai-chat.index'),
        icon: Bot,
    },
    {
        title: 'Budgets',
        href: route('budgets.index'),
        icon: PieChart,
    },
    {
        title: 'Subscriptions',
        href: route('subscriptions.index'),
        icon: RefreshCcw,
    },
    {
        title: 'Laporan AI',
        href: route('reports.index'),
        icon: BookOpen,
    },
    {
        title: 'Transaksi',
        href: route('transactions.index'),
        icon: Receipt,
    },
    {
        title: 'Scan Struk',
        href: route('receipts.index'),
        icon: Camera,
    },
    {
        title: 'Target Keuangan',
        href: route('goals.index'),
        icon: Award,
    },
    {
        title: 'Akun',
        href: route('accounts.index'),
        icon: Wallet,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
