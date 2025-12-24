import { usePage } from '@inertiajs/vue3';
import type { MaybeRefOrGetter } from 'vue';
import { computed, toValue } from 'vue';

export interface SeoMeta {
    title?: string;
    description?: string;
    canonical?: string;
    robots?: string;
    ogTitle?: string;
    ogDescription?: string;
    ogImage?: string;
    ogType?: string;
    ogUrl?: string;
    twitterCard?: string;
    twitterTitle?: string;
    twitterDescription?: string;
    twitterImage?: string;
    jsonLd?: Record<string, unknown> | Record<string, unknown>[];
}

export interface UseSeoMetaOptions {
    title?: MaybeRefOrGetter<string | undefined>;
    description?: MaybeRefOrGetter<string | undefined>;
    canonical?: MaybeRefOrGetter<string | undefined>;
    robots?: MaybeRefOrGetter<string | undefined>;
    ogImage?: MaybeRefOrGetter<string | undefined>;
    ogType?: MaybeRefOrGetter<string | undefined>;
    twitterCard?: MaybeRefOrGetter<string | undefined>;
    jsonLd?: MaybeRefOrGetter<Record<string, unknown> | Record<string, unknown>[] | undefined>;
}

interface SeoConfig {
    site_name?: string;
    title_template?: string;
    default_description?: string;
    default_og_image?: string;
    twitter_handle?: string;
    robots?: string;
    google_site_verification?: string;
}

interface PageProps {
    appUrl?: string;
    seo?: SeoConfig;
}

/**
 * Normalize a URL to be absolute
 */
function toAbsoluteUrl(path: string | undefined, baseUrl: string): string {
    if (!path) return baseUrl;
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }
    return `${baseUrl}${path.startsWith('/') ? '' : '/'}${path}`;
}

/**
 * Composable for generating SEO meta tags
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useSeoMeta } from '@/composables/useSeoMeta';
 *
 * const { headTags, jsonLdScript } = useSeoMeta({
 *   title: 'Page Title',
 *   description: 'Page description for search engines.',
 *   canonical: '/page-path',
 *   ogImage: '/images/og-page.png',
 * });
 * </script>
 *
 * <template>
 *   <Head>
 *     <title>{{ headTags.title }}</title>
 *     <meta name="description" :content="headTags.description" />
 *     <link rel="canonical" :href="headTags.canonical" />
 *     <meta name="robots" :content="headTags.robots" />
 *     <meta property="og:title" :content="headTags.ogTitle" />
 *     <meta property="og:description" :content="headTags.ogDescription" />
 *     <meta property="og:image" :content="headTags.ogImage" />
 *     <meta property="og:url" :content="headTags.ogUrl" />
 *     <meta property="og:type" :content="headTags.ogType" />
 *     <meta property="og:site_name" :content="headTags.ogSiteName" />
 *     <meta name="twitter:card" :content="headTags.twitterCard" />
 *     <meta name="twitter:title" :content="headTags.twitterTitle" />
 *     <meta name="twitter:description" :content="headTags.twitterDescription" />
 *     <meta name="twitter:image" :content="headTags.twitterImage" />
 *     <component :is="'script'" type="application/ld+json" v-html="jsonLdScript" />
 *   </Head>
 * </template>
 * ```
 */
export function useSeoMeta(options: UseSeoMetaOptions = {}) {
    const page = usePage<PageProps>();
    const seoConfig = computed(() => page.props.seo || {});
    const baseUrl = computed(() => page.props.appUrl || '');

    const headTags = computed<SeoMeta & { ogSiteName: string }>(() => {
        const config = seoConfig.value;
        const base = baseUrl.value;

        const title = toValue(options.title);
        const description = toValue(options.description) || config.default_description || '';
        const canonical = toValue(options.canonical);
        const robots = toValue(options.robots) || config.robots || 'index, follow';
        const ogImage = toValue(options.ogImage) || config.default_og_image;
        const ogType = toValue(options.ogType) || 'website';
        const twitterCard = toValue(options.twitterCard) || 'summary_large_image';

        const absoluteCanonical = canonical ? toAbsoluteUrl(canonical, base) : base;
        const absoluteOgImage = toAbsoluteUrl(ogImage, base);

        return {
            title: title || config.site_name || 'Honeymelon',
            description,
            canonical: absoluteCanonical,
            robots,
            ogTitle: title || config.site_name || 'Honeymelon',
            ogDescription: description,
            ogImage: absoluteOgImage,
            ogType,
            ogUrl: absoluteCanonical,
            ogSiteName: config.site_name || 'Honeymelon',
            twitterCard,
            twitterTitle: title || config.site_name || 'Honeymelon',
            twitterDescription: description,
            twitterImage: absoluteOgImage,
        };
    });

    const jsonLdScript = computed(() => {
        const jsonLd = toValue(options.jsonLd);
        if (!jsonLd) return '';
        return JSON.stringify(jsonLd);
    });

    return {
        headTags,
        jsonLdScript,
        baseUrl,
        seoConfig,
    };
}

/**
 * Generate SoftwareApplication JSON-LD schema
 */
export function generateSoftwareApplicationSchema(options: {
    name: string;
    description: string;
    operatingSystem: string;
    applicationCategory: string;
    url: string;
    image?: string;
    offers?: {
        price: number;
        priceCurrency: string;
    };
}): Record<string, unknown> {
    const schema: Record<string, unknown> = {
        '@context': 'https://schema.org',
        '@type': 'SoftwareApplication',
        name: options.name,
        description: options.description,
        operatingSystem: options.operatingSystem,
        applicationCategory: options.applicationCategory,
        url: options.url,
    };

    if (options.image) {
        schema.image = options.image;
    }

    if (options.offers) {
        schema.offers = {
            '@type': 'Offer',
            price: options.offers.price,
            priceCurrency: options.offers.priceCurrency,
        };
    }

    return schema;
}

/**
 * Generate Organization JSON-LD schema
 */
export function generateOrganizationSchema(options: {
    name: string;
    url: string;
    logo?: string;
}): Record<string, unknown> {
    const schema: Record<string, unknown> = {
        '@context': 'https://schema.org',
        '@type': 'Organization',
        name: options.name,
        url: options.url,
    };

    if (options.logo) {
        schema.logo = options.logo;
    }

    return schema;
}

/**
 * Generate FAQPage JSON-LD schema
 */
export function generateFaqSchema(
    faqs: Array<{ question: string; answer: string }>
): Record<string, unknown> {
    return {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: faqs.map((faq) => ({
            '@type': 'Question',
            name: faq.question,
            acceptedAnswer: {
                '@type': 'Answer',
                text: faq.answer,
            },
        })),
    };
}

export default useSeoMeta;
