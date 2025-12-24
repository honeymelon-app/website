<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DonutChart } from '@/components/ui/chart-donut';
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
    Download,
    Eye,
    KeyRound,
    Monitor,
    Package,
    Receipt,
    Smartphone,
    Tablet,
    TrendingDown,
    TrendingUp,
    Users,
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

interface VisitorAnalytics {
    total_visits: number;
    unique_visitors: number;
    visits_today: number;
    visits_change: number;
    visits_over_time: Array<{
        date: string;
        visits: number;
        unique_visitors: number;
    }>;
    visits_by_page: Array<{ page: string; visits: number; }>;
    visits_by_device: Array<{ device: string; count: number; }>;
    visits_by_browser: Array<{ browser: string; count: number; }>;
    top_referrers: Array<{ referrer: string; url: string; count: number; }>;
}

interface DownloadAnalytics {
    total_downloads: number;
    downloads_today: number;
    downloads_change: number;
    downloads_by_artifact: Array<{ artifact_name: string; count: number; }>;
    recent_downloads: Array<{
        id: string;
        artifact_name: string;
        user_email: string | null;
        downloaded_at: string;
    }>;
}

interface Props {
    metrics: Metrics;
    recent_orders: RecentOrder[];
    recent_licenses: RecentLicense[];
    charts: ChartData;
    visitor_analytics: VisitorAnalytics;
    download_analytics: DownloadAnalytics;
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

function formatPageName(routeName: string): string {
    const names: Record<string, string> = {
        home: 'Home',
        download: 'Download',
        pricing: 'Pricing',
        privacy: 'Privacy Policy',
        terms: 'Terms of Service',
    };
    return names[routeName] || routeName;
}

const revenueChartData = computed(() => {
    return props.charts.orders_over_time.map((item) => ({
        date: item.date,
        Revenue: item.revenue / 100,
    }));
});

const visitsChartData = computed(() => {
    return props.visitor_analytics.visits_over_time.map((item) => ({
        date: item.date,
        Visits: item.visits,
        'Unique Visitors': item.unique_visitors,
    }));
});

const deviceChartData = computed(() => {
    return props.visitor_analytics.visits_by_device.map((item) => ({
        device: item.device.charAt(0).toUpperCase() + item.device.slice(1),
        count: item.count,
    }));
});

const browserChartData = computed(() => {
    return props.visitor_analytics.visits_by_browser.map((item) => ({
        browser: item.browser,
        count: item.count,
    }));
});

function getDeviceIcon(device: string) {
    switch (device.toLowerCase()) {
        case 'mobile':
            return Smartphone;
        case 'tablet':
            return Tablet;
        default:
            return Monitor;
    }
}
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
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
                                {{
                                    formatCurrency(metrics.total_revenue_cents)
                                }}
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

                <!-- Total Downloads -->
                <Card class="relative overflow-hidden">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex size-10 items-center justify-center rounded-full bg-cyan-500/10"
                            >
                                <Download
                                    class="size-5 text-cyan-600 dark:text-cyan-400"
                                />
                            </div>
                            <div
                                class="flex items-center gap-1 text-sm"
                                :class="download_analytics.downloads_change >= 0
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-red-600 dark:text-red-400'
                                    "
                            >
                                <TrendingUp
                                    v-if="
                                        download_analytics.downloads_change >= 0
                                    "
                                    class="size-4"
                                />
                                <TrendingDown v-else class="size-4" />
                                {{
                                    formatChange(
                                        download_analytics.downloads_change,
                                    )
                                }}
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-muted-foreground">
                                Total Downloads
                            </p>
                            <p class="mt-1 text-3xl font-semibold">
                                {{
                                    download_analytics.total_downloads.toLocaleString()
                                }}
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
                            :y-formatter="(value: number) => formatCurrency(value * 100)
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
                                        class="truncate text-sm leading-none font-medium"
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

            <!-- Visitor Analytics Section -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold">Visitor Analytics</h2>

                <!-- Visitor Metrics -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Visits -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-sky-500/10"
                                >
                                    <Eye
                                        class="size-5 text-sky-600 dark:text-sky-400"
                                    />
                                </div>
                                <div
                                    class="flex items-center gap-1 text-sm"
                                    :class="visitor_analytics.visits_change >= 0
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-red-600 dark:text-red-400'
                                        "
                                >
                                    <TrendingUp
                                        v-if="
                                            visitor_analytics.visits_change >= 0
                                        "
                                        class="size-4"
                                    />
                                    <TrendingDown v-else class="size-4" />
                                    {{
                                        formatChange(
                                            visitor_analytics.visits_change,
                                        )
                                    }}
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-muted-foreground">
                                    Page Views (30d)
                                </p>
                                <p class="mt-1 text-3xl font-semibold">
                                    {{
                                        visitor_analytics.total_visits.toLocaleString()
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Unique Visitors -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-violet-500/10"
                                >
                                    <Users
                                        class="size-5 text-violet-600 dark:text-violet-400"
                                    />
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-muted-foreground">
                                    Unique Visitors (30d)
                                </p>
                                <p class="mt-1 text-3xl font-semibold">
                                    {{
                                        visitor_analytics.unique_visitors.toLocaleString()
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Visits Today -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-rose-500/10"
                                >
                                    <Eye
                                        class="size-5 text-rose-600 dark:text-rose-400"
                                    />
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-muted-foreground">
                                    Page Views Today
                                </p>
                                <p class="mt-1 text-3xl font-semibold">
                                    {{
                                        visitor_analytics.visits_today.toLocaleString()
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Top Page -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-teal-500/10"
                                >
                                    <Monitor
                                        class="size-5 text-teal-600 dark:text-teal-400"
                                    />
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-muted-foreground">
                                    Top Page
                                </p>
                                <p class="mt-1 text-3xl font-semibold">
                                    {{
                                        visitor_analytics.visits_by_page
                                            .length > 0
                                            ? formatPageName(
                                                visitor_analytics
                                                    .visits_by_page[0].page,
                                            )
                                            : '-'
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Visitor Charts -->
                <div class="grid gap-6 lg:grid-cols-5">
                    <!-- Visits Over Time -->
                    <Card class="lg:col-span-3">
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base font-medium">
                                    Page Views
                                </CardTitle>
                                <span class="text-sm text-muted-foreground">
                                    Last 30 days
                                </span>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <LineChart
                                v-if="visitsChartData.length > 0"
                                :data="visitsChartData"
                                index="date"
                                :categories="['Visits', 'Unique Visitors']"
                                :colors="[
                                    'var(--color-sky-500)',
                                    'var(--color-violet-500)',
                                ]"
                                :y-formatter="(value: number) => value.toLocaleString()
                                    "
                                class="h-[280px]"
                            />
                            <div
                                v-else
                                class="flex h-[280px] items-center justify-center text-sm text-muted-foreground"
                            >
                                No visitor data yet
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pages & Devices -->
                    <Card class="lg:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base font-medium">
                                Popular Pages
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div
                                v-if="
                                    visitor_analytics.visits_by_page.length ===
                                    0
                                "
                                class="flex h-[280px] items-center justify-center text-sm text-muted-foreground"
                            >
                                No page data yet
                            </div>
                            <div v-else class="divide-y">
                                <div
                                    v-for="page in visitor_analytics.visits_by_page.slice(
                                        0,
                                        5,
                                    )"
                                    :key="page.page"
                                    class="flex items-center justify-between px-6 py-3"
                                >
                                    <span class="text-sm">{{
                                        formatPageName(page.page)
                                    }}</span>
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        {{ page.visits.toLocaleString() }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Device & Browser Breakdown -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Devices -->
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base font-medium">
                                Devices
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="deviceChartData.length > 0"
                                class="flex items-center gap-6"
                            >
                                <DonutChart
                                    :data="deviceChartData"
                                    index="device"
                                    category="count"
                                    :colors="[
                                        'var(--color-sky-500)',
                                        'var(--color-violet-500)',
                                        'var(--color-amber-500)',
                                    ]"
                                    class="h-[180px] w-[180px]"
                                    :show-legend="false"
                                />
                                <div class="flex-1 space-y-3">
                                    <div
                                        v-for="item in deviceChartData"
                                        :key="item.device"
                                        class="flex items-center gap-3"
                                    >
                                        <component
                                            :is="getDeviceIcon(item.device)"
                                            class="size-4 text-muted-foreground"
                                        />
                                        <span class="flex-1 text-sm">{{
                                            item.device
                                        }}</span>
                                        <span
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            {{ item.count.toLocaleString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="flex h-[180px] items-center justify-center text-sm text-muted-foreground"
                            >
                                No device data yet
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Browsers -->
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base font-medium">
                                Browsers
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="browserChartData.length > 0"
                                class="flex items-center gap-6"
                            >
                                <DonutChart
                                    :data="browserChartData"
                                    index="browser"
                                    category="count"
                                    :colors="[
                                        'var(--color-emerald-500)',
                                        'var(--color-blue-500)',
                                        'var(--color-orange-500)',
                                        'var(--color-pink-500)',
                                        'var(--color-indigo-500)',
                                    ]"
                                    class="h-[180px] w-[180px]"
                                    :show-legend="false"
                                />
                                <div class="flex-1 space-y-3">
                                    <div
                                        v-for="item in browserChartData"
                                        :key="item.browser"
                                        class="flex items-center gap-3"
                                    >
                                        <span class="flex-1 text-sm">{{
                                            item.browser
                                        }}</span>
                                        <span
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            {{ item.count.toLocaleString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="flex h-[180px] items-center justify-center text-sm text-muted-foreground"
                            >
                                No browser data yet
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Top Referrers -->
                <Card v-if="visitor_analytics.top_referrers.length > 0">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base font-medium">
                            Top Referrers
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="divide-y">
                            <div
                                v-for="referrer in visitor_analytics.top_referrers.slice(
                                    0,
                                    5,
                                )"
                                :key="referrer.url"
                                class="flex items-center justify-between px-6 py-3"
                            >
                                <span class="truncate text-sm">{{
                                    referrer.referrer
                                }}</span>
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    {{ referrer.count.toLocaleString() }}
                                </span>
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
