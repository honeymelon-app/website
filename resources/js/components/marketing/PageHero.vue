<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';

interface Props {
    badge?: string;
    badgeIcon?: any;
    title: string;
    highlightedText?: string;
    description: string;
}

defineProps<Props>();
</script>

<template>
    <section
        class="relative overflow-hidden border-b border-border/40 py-32 sm:py-40"
    >
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.primary.DEFAULT/20%),transparent)]"
        />
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center text-center">
                <Badge
                    v-if="badge"
                    variant="outline"
                    class="mb-8 border-primary/50 px-4 py-1.5 text-sm"
                >
                    <component
                        v-if="badgeIcon"
                        :is="badgeIcon"
                        class="mr-2 h-3.5 w-3.5"
                    />
                    {{ badge }}
                </Badge>
                <h1
                    class="mb-8 max-w-4xl bg-gradient-to-br from-foreground to-foreground/70 bg-clip-text text-4xl leading-tight font-bold tracking-tight text-transparent sm:text-5xl md:text-6xl lg:text-7xl lg:leading-tight"
                >
                    {{ title }}
                    <span
                        v-if="highlightedText"
                        class="bg-gradient-to-r from-primary via-primary/80 to-primary bg-clip-text"
                        >{{ highlightedText }}</span
                    >
                </h1>
                <p
                    class="mb-10 max-w-2xl text-lg leading-relaxed text-muted-foreground sm:text-xl"
                >
                    {{ description }}
                </p>
                <slot name="actions" />
                <slot name="footer" />
            </div>
        </div>
    </section>
</template>
