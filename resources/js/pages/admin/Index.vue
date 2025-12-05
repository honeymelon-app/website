<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AreaChart } from '@/components/ui/chart-area';
import { BarChart } from '@/components/ui/chart-bar';
import { DonutChart } from '@/components/ui/chart-donut';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/formatters';
import { getStatusVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowUpRight,
    DollarSign,
    PackageOpen,
    ShieldCheck,
    TrendingUp,
} from 'lucide-vue-next';

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

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
                    <p class="text-muted-foreground">
                        Overview of your platform metrics and activity
                    </p>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Orders -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Orders</CardTitle
                        >
                        <PackageOpen class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ metrics.total_orders }}
                        </div>
                        <p class="flex items-center gap-1 text-xs">
                            <ArrowUpRight
                                v-if="metrics.orders_change >= 0"
                                class="size-4 text-green-500"
                            />
                            <ArrowDownRight
                                v-else
                                class="size-4 text-red-500"
                            />
                            <span
                                :class="metrics.orders_change >= 0
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                    "
                            >
                                {{ Math.abs(metrics.orders_change) }}%
                            </span>
                            <span class="text-muted-foreground">
                                from last month
                            </span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Revenue -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Revenue</CardTitle
                        >
                        <DollarSign class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(metrics.total_revenue_cents) }}
                        </div>
                        <p class="flex items-center gap-1 text-xs">
                            <ArrowUpRight
                                v-if="metrics.revenue_change >= 0"
                                class="size-4 text-green-500"
                            />
                            <ArrowDownRight
                                v-else
                                class="size-4 text-red-500"
                            />
                            <span
                                :class="metrics.revenue_change >= 0
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                    "
                            >
                                {{ Math.abs(metrics.revenue_change) }}%
                            </span>
                            <span class="text-muted-foreground">
                                from last month
                            </span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Licenses -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Active Licenses</CardTitle
                        >
                        <ShieldCheck class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ metrics.active_licenses }}
                        </div>
                        <p class="flex items-center gap-1 text-xs">
                            <ArrowUpRight
                                v-if="metrics.licenses_change >= 0"
                                class="size-4 text-green-500"
                            />
                            <ArrowDownRight
                                v-else
                                class="size-4 text-red-500"
                            />
                            <span
                                :class="metrics.licenses_change >= 0
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                    "
                            >
                                {{ Math.abs(metrics.licenses_change) }}%
                            </span>
                            <span class="text-muted-foreground">
                                from last month
                            </span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Releases -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Published Releases</CardTitle
                        >
                        <TrendingUp class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ metrics.total_releases }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            All-time published releases
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-4 lg:grid-cols-7">
                <!-- Orders & Revenue Over Time -->
                <Card class="lg:col-span-4">
                    <CardHeader>
                        <CardTitle>Orders & Revenue (Last 30 Days)</CardTitle>
                    </CardHeader>
                    <CardContent class="pl-2">
                        <AreaChart
                            v-if="charts.orders_over_time.length > 0"
                            :data="charts.orders_over_time"
                            index="date"
                            :categories="['orders', 'revenue']"
                            :colors="[
                                'hsl(var(--chart-1))',
                                'hsl(var(--chart-2))',
                            ]"
                            :y-formatter="(tick: number | Date) => {
                                    return typeof tick === 'number'
                                        ? tick.toFixed(0)
                                        : '';
                                }
                                "
                            class="h-[300px]"
                        />
                        <div
                            v-else
                            class="flex h-[300px] items-center justify-center text-sm text-muted-foreground"
                        >
                            No data available
                        </div>
                    </CardContent>
                </Card>

                <!-- License Status Distribution -->
                <Card class="lg:col-span-3">
                    <CardHeader>
                        <CardTitle>License Status Distribution</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DonutChart
                            v-if="charts.licenses_by_status.length > 0"
                            :data="charts.licenses_by_status"
                            index="status"
                            category="count"
                            :colors="[
                                'hsl(var(--chart-1))',
                                'hsl(var(--chart-2))',
                                'hsl(var(--chart-3))',
                            ]"
                            class="mx-auto h-[300px]"
                        />
                        <div
                            v-else
                            class="flex h-[300px] items-center justify-center text-sm text-muted-foreground"
                        >
                            No data available
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Platform Distribution -->
            <div class="grid gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Artifacts by Platform</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <BarChart
                            v-if="charts.artifacts_by_platform.length > 0"
                            :data="charts.artifacts_by_platform"
                            index="platform"
                            :categories="['count']"
                            :colors="['hsl(var(--chart-1))']"
                            class="h-[250px]"
                        />
                        <div
                            v-else
                            class="flex h-[250px] items-center justify-center text-sm text-muted-foreground"
                        >
                            No data available
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Orders -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Orders</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-if="recent_orders.length === 0"
                                class="flex h-[250px] items-center justify-center text-sm text-muted-foreground"
                            >
                                No orders yet
                            </div>
                            <div
                                v-for="order in recent_orders"
                                :key="order.id"
                                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        {{ order.email }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge
                                        v-if="order.license_status"
                                        :variant="getStatusVariant(
                                            order.license_status,
                                        )
                                            "
                                    >
                                        {{ order.license_status }}
                                    </Badge>
                                    <div class="font-medium">
                                        {{ order.formatted_amount }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Licenses -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Licenses</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-if="recent_licenses.length === 0"
                            class="flex h-[100px] items-center justify-center text-sm text-muted-foreground"
                        >
                            No licenses issued yet
                        </div>
                        <div
                            v-for="(license, index) in recent_licenses"
                            :key="license.id"
                        >
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p
                                        class="font-mono text-sm leading-none font-medium"
                                    >
                                        {{ license.key_plain }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Max Version:
                                        {{ license.max_major_version }} •
                                        {{ formatDate(license.created_at) }}
                                    </p>
                                </div>
                                <Badge
                                    :variant="getStatusVariant(license.status)"
                                >
                                    {{ license.status }}
                                </Badge>
                            </div>
                            <Separator
                                v-if="index < recent_licenses.length - 1"
                                class="my-4"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
