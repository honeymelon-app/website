<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { preview, sync, update } from '@/routes/product';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

interface ProductData {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    stripe_product_id: string | null;
    stripe_price_id: string | null;
    price_cents: number;
    currency: string;
    is_active: boolean;
}

interface Props {
    product: ProductData | null;
}

interface Flash {
    success?: string;
    error?: string;
    info?: string;
    stripe_preview?: {
        name: string;
        description: string | null;
        stripe_product_id: string;
        stripe_price_id: string | null;
        price_cents: number;
        currency: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    name: props.product?.name ?? '',
    description: props.product?.description ?? '',
    stripe_product_id: props.product?.stripe_product_id ?? '',
    is_active: props.product?.is_active ?? true,
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Product settings',
        href: '/settings/product',
    },
];

const syncing = ref(false);
const previewing = ref(false);
const stripePreview = ref<Flash['stripe_preview']>(null);

const displayStripePriceId = computed(() => {
    return props.product?.stripe_price_id || 'Not set';
});

const displayCurrentPrice = computed(() => {
    return props.product?.price_cents ?? 0;
});

const displayCurrency = computed(() => {
    return props.product?.currency ?? 'usd';
});

// Watch for product changes and update form
watch(
    () => props.product,
    (newProduct) => {
        if (newProduct) {
            form.name = newProduct.name;
            form.description = newProduct.description ?? '';
            form.stripe_product_id = newProduct.stripe_product_id ?? '';
            form.is_active = newProduct.is_active;
        }
    },
    { deep: true },
);

const formatPrice = (cents: number): string => {
    return (cents / 100).toFixed(2);
};

const submit = () => {
    form.put(update().url, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.flash as Flash | undefined;
            if (flash?.error) {
                toast.error(flash.error);
            } else if (flash?.success) {
                toast.success(flash.success);
            } else {
                toast.success('Product settings updated successfully.');
            }
        },
        onError: () => toast.error('Failed to update product settings.'),
    });
};

const syncFromStripe = () => {
    syncing.value = true;
    stripePreview.value = null;
    router.post(
        sync().url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Product synced from Stripe successfully.');
                router.reload({ only: ['product'] });
            },
            onError: () => toast.error('Failed to sync from Stripe.'),
            onFinish: () => {
                syncing.value = false;
            },
        },
    );
};

const previewFromStripe = () => {
    previewing.value = true;
    router.post(
        preview().url,
        {},
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash as Flash | undefined;
                if (flash?.stripe_preview) {
                    stripePreview.value = flash.stripe_preview;
                    toast.success('Preview loaded from Stripe.');
                } else if (flash?.error) {
                    toast.error(flash.error);
                }
            },
            onError: () => toast.error('Failed to fetch preview from Stripe.'),
            onFinish: () => {
                previewing.value = false;
            },
        },
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Product settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Product information"
                    description="Manage your product details and Stripe integration."
                />

                <!-- Info Banner -->
                <div
                    v-if="product?.stripe_product_id"
                    class="rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950"
                >
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        <strong class="font-medium">Stripe Integration:</strong>
                        Product name, description, and active status will be
                        pushed to Stripe when you save. Pricing is managed in
                        Stripe and must be synced using the "Sync from Stripe"
                        button below.
                    </p>
                </div>

                <!-- Stripe Preview -->
                <div
                    v-if="stripePreview"
                    class="rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-950"
                >
                    <h4
                        class="mb-2 text-sm font-medium text-yellow-800 dark:text-yellow-200"
                    >
                        Preview from Stripe
                    </h4>
                    <dl
                        class="space-y-1 text-sm text-yellow-700 dark:text-yellow-300"
                    >
                        <div class="flex gap-2">
                            <dt class="font-medium">Name:</dt>
                            <dd>{{ stripePreview.name }}</dd>
                        </div>
                        <div
                            v-if="stripePreview.description"
                            class="flex gap-2"
                        >
                            <dt class="font-medium">Description:</dt>
                            <dd>{{ stripePreview.description }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="font-medium">Price:</dt>
                            <dd>
                                ${{ formatPrice(stripePreview.price_cents) }}
                                {{ stripePreview.currency.toUpperCase() }}
                            </dd>
                        </div>
                        <div
                            v-if="stripePreview.stripe_price_id"
                            class="flex gap-2"
                        >
                            <dt class="font-medium">Price ID:</dt>
                            <dd class="font-mono text-xs">
                                {{ stripePreview.stripe_price_id }}
                            </dd>
                        </div>
                    </dl>
                    <p
                        class="mt-3 text-xs text-yellow-600 dark:text-yellow-400"
                    >
                        Click "Sync from Stripe" to update your local product
                        with these values.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Product name</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            v-model="form.name"
                            class="max-w-md"
                            placeholder="Honeymelon"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            class="max-w-md"
                            placeholder="A beautiful video converter for macOS..."
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="mb-4 text-sm font-medium">
                            Stripe Integration
                        </h3>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="stripe_product_id"
                                    >Stripe Product ID</Label
                                >
                                <Input
                                    id="stripe_product_id"
                                    type="text"
                                    v-model="form.stripe_product_id"
                                    class="max-w-md font-mono text-sm"
                                    placeholder="prod_..."
                                />
                                <p class="text-xs text-muted-foreground">
                                    Find this in your Stripe Dashboard →
                                    Products. Changes will be automatically
                                    synced to Stripe when you save.
                                </p>
                                <InputError
                                    :message="form.errors.stripe_product_id"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="stripe_price_id"
                                    >Stripe Price ID (Read-only)</Label
                                >
                                <Input
                                    id="stripe_price_id"
                                    type="text"
                                    :value="displayStripePriceId"
                                    class="max-w-md font-mono text-sm"
                                    disabled
                                    readonly
                                />
                                <p class="text-xs text-muted-foreground">
                                    This is synced from Stripe's default price.
                                    Use "Sync from Stripe" to update.
                                </p>
                            </div>

                            <div class="flex max-w-md gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    :disabled="
                                        !form.stripe_product_id || previewing
                                    "
                                    @click="previewFromStripe"
                                >
                                    <Spinner v-if="previewing" />
                                    Preview
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    :disabled="
                                        !form.stripe_product_id || syncing
                                    "
                                    @click="syncFromStripe"
                                >
                                    <Spinner v-if="syncing" />
                                    Sync from Stripe
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="mb-4 text-sm font-medium">
                            Pricing (Managed in Stripe)
                        </h3>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label>Current Price</Label>
                                <div class="text-2xl font-semibold">
                                    ${{ formatPrice(displayCurrentPrice) }}
                                    <span
                                        class="text-sm font-normal text-muted-foreground"
                                    >
                                        {{ displayCurrency.toUpperCase() }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Pricing is managed in your Stripe Dashboard.
                                    Use "Sync from Stripe" above to update the
                                    local price.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <div class="flex items-center gap-3">
                            <Switch id="is_active" v-model="form.is_active" />
                            <Label for="is_active" class="cursor-pointer">
                                Product is active
                            </Label>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Inactive products cannot be purchased
                        </p>
                        <InputError :message="form.errors.is_active" />
                    </div>

                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" />
                        Save changes
                    </Button>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
