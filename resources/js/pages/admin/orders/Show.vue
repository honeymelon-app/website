<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import licensesRoute from '@/routes/admin/licenses';
import ordersRoute from '@/routes/admin/orders';
import type { BreadcrumbItem } from '@/types';
import type { License, Order } from '@/types/resources';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    CheckCircle,
    CreditCard,
    ExternalLink,
    Mail,
    RotateCcw,
    Shield,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ExtendedOrder extends Order {
    license?: License | null;
}

interface Props {
    order: ExtendedOrder;
}

const props = defineProps<Props>();

const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Orders',
        href: ordersRoute.index().url,
    },
    {
        title: props.order.id.substring(0, 8) + '...',
        href: ordersRoute.show(props.order.id).url,
    },
];

// Refund dialog state
const showRefundDialog = ref(false);
const refundForm = useForm({
    reason: '',
});

// Flash messages
const successMessage = computed(
    () => page.props.flash?.success as string | undefined,
);
const errorMessage = computed(
    () => page.props.flash?.error as string | undefined,
);

// Helper to format currency
const formatCurrency = (
    amountCents: number | null,
    currency: string | null,
): string => {
    if (amountCents === null) return 'N/A';
    const amount = amountCents / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency?.toUpperCase() || 'USD',
    }).format(amount);
};

// Helper to format date
const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Helper to get provider badge variant
const getProviderVariant = (
    provider: string,
): 'default' | 'secondary' | 'outline' => {
    const variantMap: Record<string, 'default' | 'secondary' | 'outline'> = {
        stripe: 'default',
        manual: 'secondary',
    };
    return variantMap[provider] || 'outline';
};

// Helper to get status badge variant
const getStatusVariant = (
    status: string,
): 'default' | 'secondary' | 'destructive' => {
    const variantMap: Record<string, 'default' | 'secondary' | 'destructive'> =
        {
            active: 'default',
            revoked: 'destructive',
            expired: 'secondary',
        };
    return variantMap[status] || 'secondary';
};

// Get Stripe dashboard URL for the order
const getStripeUrl = (): string | null => {
    if (props.order.provider !== 'stripe' || !props.order.external_id) {
        return null;
    }
    // Stripe checkout session IDs start with cs_
    if (props.order.external_id.startsWith('cs_')) {
        return `https://dashboard.stripe.com/checkout/sessions/${props.order.external_id}`;
    }
    return null;
};

// Navigate to license
const viewLicense = (): void => {
    if (props.order.license?.id) {
        router.visit(licensesRoute.show(props.order.license.id).url);
    }
};

// Refund actions
const confirmRefund = (): void => {
    showRefundDialog.value = true;
};

const processRefund = (): void => {
    refundForm.post(ordersRoute.refund(props.order.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showRefundDialog.value = false;
            refundForm.reset();
        },
    });
};

const cancelRefund = (): void => {
    showRefundDialog.value = false;
    refundForm.reset();
};
</script>

<template>
    <Head :title="`Order ${order.id.substring(0, 8)}...`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-6">
                <!-- Flash Messages -->
                <div
                    v-if="successMessage"
                    class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950"
                >
                    <CheckCircle
                        class="h-5 w-5 text-green-600 dark:text-green-400"
                    />
                    <p
                        class="text-sm font-medium text-green-800 dark:text-green-200"
                    >
                        {{ successMessage }}
                    </p>
                </div>
                <div
                    v-if="errorMessage"
                    class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950"
                >
                    <AlertTriangle
                        class="h-5 w-5 text-red-600 dark:text-red-400"
                    />
                    <p
                        class="text-sm font-medium text-red-800 dark:text-red-200"
                    >
                        {{ errorMessage }}
                    </p>
                </div>

                <!-- Refunded Banner -->
                <div
                    v-if="order.is_refunded"
                    class="flex items-center gap-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-950"
                >
                    <RotateCcw
                        class="h-5 w-5 text-yellow-600 dark:text-yellow-400"
                    />
                    <div>
                        <p
                            class="font-medium text-yellow-800 dark:text-yellow-200"
                        >
                            This order has been refunded
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Refunded on {{ formatDate(order.refunded_at!) }}
                            <span v-if="order.refund_id" class="font-mono">
                                ({{ order.refund_id }})
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Header -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="router.visit(ordersRoute.index().url)"
                                class="h-8 w-8"
                            >
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                            <h3 class="text-2xl font-semibold tracking-tight">
                                Order Details
                            </h3>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            View order information and associated license.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Badge
                            v-if="order.is_refunded"
                            variant="outline"
                            class="border-yellow-500 text-yellow-600"
                        >
                            Refunded
                        </Badge>
                        <Badge
                            :variant="getProviderVariant(order.provider)"
                            class="capitalize"
                        >
                            {{ order.provider }}
                        </Badge>
                        <Button
                            v-if="getStripeUrl()"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <a
                                :href="getStripeUrl()!"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <ExternalLink class="mr-2 h-4 w-4" />
                                View in Stripe
                            </a>
                        </Button>
                        <Button
                            v-if="order.can_be_refunded"
                            variant="destructive"
                            size="sm"
                            @click="confirmRefund"
                        >
                            <RotateCcw class="mr-2 h-4 w-4" />
                            Refund Order
                        </Button>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Order Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CreditCard class="h-5 w-5" />
                                Order Information
                            </CardTitle>
                            <CardDescription>
                                Payment and customer details
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >Order ID</span
                                >
                                <code
                                    class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                                >
                                    {{ order.id }}
                                </code>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">Email</span>
                                <div class="flex items-center gap-2">
                                    <Mail
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <a
                                        :href="`mailto:${order.email}`"
                                        class="text-sm hover:underline"
                                    >
                                        {{ order.email }}
                                    </a>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">Amount</span>
                                <span
                                    class="text-lg font-semibold"
                                    :class="{
                                        'text-muted-foreground line-through':
                                            order.is_refunded,
                                    }"
                                >
                                    {{
                                        formatCurrency(
                                            order.amount_cents,
                                            order.currency,
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >Provider</span
                                >
                                <Badge
                                    :variant="
                                        getProviderVariant(order.provider)
                                    "
                                    class="w-fit capitalize"
                                >
                                    {{ order.provider }}
                                </Badge>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >External ID</span
                                >
                                <code
                                    class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                                >
                                    {{ order.external_id }}
                                </code>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">Created</span>
                                <span class="text-sm text-muted-foreground">
                                    {{ formatDate(order.created_at) }}
                                </span>
                            </div>
                            <div
                                v-if="
                                    !order.is_within_refund_window &&
                                    !order.is_refunded
                                "
                                class="flex flex-col gap-1"
                            >
                                <span
                                    class="text-sm font-medium text-yellow-600 dark:text-yellow-400"
                                >
                                    ⚠️ Outside 30-day refund window
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Associated License -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Shield class="h-5 w-5" />
                                Associated License
                            </CardTitle>
                            <CardDescription>
                                License issued for this order
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <template v-if="order.license">
                                <div class="flex flex-col gap-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium"
                                            >License ID</span
                                        >
                                        <code
                                            class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                                        >
                                            {{ order.license.id }}
                                        </code>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium"
                                            >Status</span
                                        >
                                        <Badge
                                            :variant="
                                                getStatusVariant(
                                                    order.license.status,
                                                )
                                            "
                                            class="w-fit capitalize"
                                        >
                                            {{ order.license.status }}
                                        </Badge>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium"
                                            >Max Version</span
                                        >
                                        <span
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                order.license
                                                    .max_major_version === 999
                                                    ? 'Lifetime (All versions)'
                                                    : `v${order.license.max_major_version}.x`
                                            }}
                                        </span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium"
                                            >Issued At</span
                                        >
                                        <span
                                            class="text-sm text-muted-foreground"
                                        >
                                            <template
                                                v-if="order.license.issued_at"
                                            >
                                                {{
                                                    formatDate(
                                                        order.license.issued_at,
                                                    )
                                                }}
                                            </template>
                                            <template v-else
                                                >Not issued yet</template
                                            >
                                        </span>
                                    </div>
                                    <Button
                                        variant="link"
                                        class="h-auto justify-start p-0 text-sm"
                                        @click="viewLicense"
                                    >
                                        <Shield class="mr-2 h-4 w-4" />
                                        View License Details
                                    </Button>
                                </div>
                            </template>
                            <template v-else>
                                <div
                                    class="flex h-32 items-center justify-center text-sm text-muted-foreground italic"
                                >
                                    No license associated with this order.
                                </div>
                            </template>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Refund Confirmation Dialog -->
        <AlertDialog
            :open="showRefundDialog"
            @update:open="showRefundDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Refund Order</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to refund this order for
                        <strong>{{
                            formatCurrency(order.amount_cents, order.currency)
                        }}</strong
                        >?
                        <span class="mt-2 block font-medium text-destructive">
                            This will also revoke the associated license. This
                            action cannot be undone.
                        </span>
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <div class="space-y-2 py-4">
                    <Label for="reason">Reason for refund (optional)</Label>
                    <Input
                        id="reason"
                        v-model="refundForm.reason"
                        placeholder="e.g., Customer requested refund within 30-day guarantee"
                    />
                </div>

                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelRefund">
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="processRefund"
                        class="bg-destructive hover:bg-destructive/90"
                        :disabled="refundForm.processing"
                    >
                        {{
                            refundForm.processing
                                ? 'Processing...'
                                : 'Refund Order'
                        }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
