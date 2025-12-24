<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LineChart } from '@/components/ui/chart-line';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/formatters';
import { getStatusVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import licensesRoute from '@/routes/admin/licenses';
import ordersRoute from '@/routes/admin/orders';
import releasesRoute from '@/routes/admin/releases';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    DollarSign,
    KeyRound,
    Package,
    Receipt,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Metrics {
    total_orders: number;
    total_revenue_cents: number;
    active_licenses: number;
    total_releases: number;
    orders_change: number;
    revenue_change: number;
    licenses_change: number;
}

interface RecentOrder {
    id: string;
    email: string;
    amount_cents: number;
    formatted_amount: string;
    currency: string;
    created_at: string;
    license_status?: string;
}

interface RecentLicense {
    id: string;
    key_plain: string;
    status: string;
    max_major_version: number;
    created_at: string;
}

interface ChartData {
    orders_over_time: Array<{ date: string; orders: number; revenue: number; }>;
    licenses_by_status: Array<{ status: string; count: number; }>;
    artifacts_by_platform: Array<{ platform: string; count: number; }>;
}

interface Props {
    metrics: Metrics;
    recent_orders: RecentOrder[];
    recent_licenses: RecentLicense[];
    charts: ChartData;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage<SharedData>();

const userName = computed(() => {
    const name = page.props.auth.user?.name ?? 'there';
    return name.split(' ')[0];
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) {
        return 'Good morning';
    }
    if (hour < 18) {
        return 'Good afternoon';
    }
    return 'Good evening';
});

function formatChange(value: number): string {
    const sign = value >= 0 ? '+' : '';
    return `${sign}${value.toFixed(1)}%`;
}

function getInitials(email: string): string {
    const parts = email.split('@')[0].split(/[._-]/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0].substring(0, 2).toUpperCase();
}

function getRelativeTime(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
        return 'Just now';
    }
    if (diffMins < 60) {
        return `${diffMins}m ago`;
    }
    if (diffHours < 24) {
        return `${diffHours}h ago`;
    }
    if (diffDays < 7) {
        return `${diffDays}d ago`;
    }
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

const revenueChartData = computed(() => {
    return props.charts.orders_over_time.map((item) => ({
        date: item.date,
        Revenue: item.revenue / 100,
    }));
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ greeting }}, {{ userName }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    Here's what's happening with your platform today.
                </p>
            </div>

            <!-- Metrics Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Revenue -->
                <Card class="relative overflow-hidden">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex size-10 items-center justify-center rounded-full bg-emerald-500/10"
                            >
                                <DollarSign
                                    class="size-5 text-emerald-600 dark:text-emerald-400"
                                />
                            </div>
                            <div
                                class="flex items-center gap-1 text-sm"
                                :class="metrics.revenue_change >= 0
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-red-600 dark:text-red-400'
                                    "
                            >
                                <TrendingUp
                                    v-if="metrics.revenue_change >= 0"
                                    class="size-4"
                                />
                                <TrendingDown v-else class="size-4" />
                                {{ formatChange(metrics.revenue_change) }}
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-muted-foreground">
                                Total Revenue
                            </p>
                            <p class="mt-1 text-3xl font-semibold">
                                {{ formatCurrency(metrics.total_revenue_cents) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Orders -->
                <Card class="relative overflow-hidden">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex size-10 items-center justify-center rounded-full bg-blue-500/10"
                            >
                                <Receipt
                                    class="size-5 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                            <div
                                class="flex items-center gap-1 text-sm"
                                :class="metrics.orders_change >= 0
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-red-600 dark:text-red-400'
                                    "
                            >
                                <TrendingUp
                                    v-if="metrics.orders_change >= 0"
                                    class="size-4"
                                />
                                <TrendingDown v-else class="size-4" />
                                {{ formatChange(metrics.orders_change) }}
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-muted-foreground">
                                Total Orders
                            </p>
                            <p class="mt-1 text-3xl font-semibold">
                                {{ metrics.total_orders.toLocaleString() }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Active Licenses -->
                <Card class="relative overflow-hidden">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex size-10 items-center justify-center rounded-full bg-amber-500/10"
                            >
                                <KeyRound
                                    class="size-5 text-amber-600 dark:text-amber-400"
                                />
                            </div>
                            <div
                                class="flex items-center gap-1 text-sm"
                                :class="metrics.licenses_change >= 0
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-red-600 dark:text-red-400'
                                    "
                            >
                                <TrendingUp
                                    v-if="metrics.licenses_change >= 0"
                                    class="size-4"
                                />
                                <TrendingDown v-else class="size-4" />
                                {{ formatChange(metrics.licenses_change) }}
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-muted-foreground">
                                Active Licenses
                            </p>
                            <p class="mt-1 text-3xl font-semibold">
                                {{ metrics.active_licenses.toLocaleString() }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Releases -->
                <Card class="relative overflow-hidden">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex size-10 items-center justify-center rounded-full bg-purple-500/10"
                            >
                                <Package
                                    class="size-5 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-muted-foreground">
                                Published Releases
                            </p>
                            <p class="mt-1 text-3xl font-semibold">
                                {{ metrics.total_releases.toLocaleString() }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content -->
            <div class="grid gap-6 lg:grid-cols-5">
                <!-- Revenue Chart -->
                <Card class="lg:col-span-3">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base font-medium">
                                Revenue
                            </CardTitle>
                            <span class="text-sm text-muted-foreground">
                                Last 30 days
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <LineChart
                            v-if="revenueChartData.length > 0"
                            :data="revenueChartData"
                            index="date"
                            :categories="['Revenue']"
                            :colors="['var(--color-honey-500)']"
                            :y-formatter="(value: number) =>
                                formatCurrency(value * 100)
                                "
                            :show-legend="false"
                            class="h-[280px]"
                        />
                        <div
                            v-else
                            class="flex h-[280px] items-center justify-center text-sm text-muted-foreground"
                        >
                            No revenue data yet
                        </div>
                    </CardContent>
                </Card>

                <!-- Activity Feed -->
                <Card class="lg:col-span-2">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base font-medium">
                                Recent Activity
                            </CardTitle>
                            <Link
                                :href="ordersRoute.index().url"
                                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                            >
                                View all
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div
                            v-if="
                                recent_orders.length === 0 &&
                                recent_licenses.length === 0
                            "
                            class="flex h-[280px] items-center justify-center text-sm text-muted-foreground"
                        >
                            No recent activity
                        </div>
                        <div v-else class="divide-y">
                            <div
                                v-for="order in recent_orders.slice(0, 5)"
                                :key="order.id"
                                class="flex items-center gap-3 px-6 py-3"
                            >
                                <Avatar class="size-9">
                                    <AvatarFallback
                                        class="bg-muted text-xs font-medium"
                                    >
                                        {{ getInitials(order.email) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-medium leading-none"
                                    >
                                        {{ order.email }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        New order •
                                        {{ getRelativeTime(order.created_at) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold">
                                        {{ order.formatted_amount }}
                                    </p>
                                    <Badge
                                        v-if="order.license_status"
                                        :variant="getStatusVariant(
                                            order.license_status,
                                        )
                                            "
                                        class="mt-1"
                                    >
                                        {{ order.license_status }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 sm:grid-cols-3">
                <Link
                    :href="ordersRoute.index().url"
                    class="group rounded-lg border bg-card p-5 transition-colors hover:bg-accent"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-blue-500/10"
                        >
                            <Receipt
                                class="size-5 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                        <ArrowRight
                            class="size-5 text-muted-foreground transition-transform group-hover:translate-x-1"
                        />
                    </div>
                    <h3 class="mt-4 font-medium">Orders</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        View and manage customer orders
                    </p>
                </Link>

                <Link
                    :href="licensesRoute.index().url"
                    class="group rounded-lg border bg-card p-5 transition-colors hover:bg-accent"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-amber-500/10"
                        >
                            <KeyRound
                                class="size-5 text-amber-600 dark:text-amber-400"
                            />
                        </div>
                        <ArrowRight
                            class="size-5 text-muted-foreground transition-transform group-hover:translate-x-1"
                        />
                    </div>
                    <h3 class="mt-4 font-medium">Licenses</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Manage license keys and activations
                    </p>
                </Link>

                <Link
                    :href="releasesRoute.index().url"
                    class="group rounded-lg border bg-card p-5 transition-colors hover:bg-accent"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-purple-500/10"
                        >
                            <Package
                                class="size-5 text-purple-600 dark:text-purple-400"
                            />
                        </div>
                        <ArrowRight
                            class="size-5 text-muted-foreground transition-transform group-hover:translate-x-1"
                        />
                    </div>
                    <h3 class="mt-4 font-medium">Releases</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Publish and manage app releases
                    </p>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
