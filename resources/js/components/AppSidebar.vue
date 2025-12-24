<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import artifacts from '@/routes/admin/artifacts';
import licenses from '@/routes/admin/licenses';
import orders from '@/routes/admin/orders';
import releases from '@/routes/admin/releases';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Gauge,
    Github,
    HardDrive,
    KeyRound,
    Receipt,
    Rocket,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: Gauge,
    },
    {
        title: 'Releases',
        href: releases.index(),
        icon: Rocket,
    },
    {
        title: 'Orders',
        href: orders.index(),
        icon: Receipt,
    },
    {
        title: 'Licenses',
        href: licenses.index(),
        icon: KeyRound,
    },
    {
        title: 'Artifacts',
        href: artifacts.index(),
        icon: HardDrive,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/honeymelon-app/honeymelon',
        icon: Github,
    },
    {
        title: 'Documentation',
        href: 'https://docs.honeymelon.app',
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
                        <Link :href="dashboard()">
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
