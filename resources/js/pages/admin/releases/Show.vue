<script setup lang="ts">
import { computed, h } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import releases from '@/routes/admin/releases'
import type { BreadcrumbItem } from '@/types'
import type { Release } from '@/types/api'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, Download, Package } from 'lucide-vue-next'

interface Props {
  release: Release
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Releases',
    href: releases.index().url,
  },
  {
    title: props.release.version,
    href: releases.show(props.release.id).url,
  },
]

const formattedPublishedDate = computed(() => {
  const date = new Date(props.release.published_at)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
})

const formattedCreatedDate = computed(() => {
  const date = new Date(props.release.created_at)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

const goBack = () => {
  router.visit(releases.index().url)
}
</script>

<template>
  <Head :title="`Release ${release.version}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
              <Button variant="ghost" size="icon" @click="goBack" class="h-8 w-8">
                <ArrowLeft class="h-4 w-4" />
              </Button>
              <h3 class="text-2xl font-semibold tracking-tight">
                Release {{ release.version }}
              </h3>
            </div>
            <p class="text-sm text-muted-foreground">
              View release details, artifacts, and updates.
            </p>
          </div>
          <div class="flex gap-2">
            <Badge :variant="release.channel === 'stable' ? 'default' : 'secondary'" class="capitalize">
              {{ release.channel }}
            </Badge>
            <Badge v-if="release.major" variant="destructive">
              Major Release
            </Badge>
          </div>
        </div>

        <!-- Release Info -->
        <div class="grid gap-4 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Release Information</CardTitle>
              <CardDescription>Basic details about this release</CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col gap-4">
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Version</span>
                <span class="font-mono text-sm text-muted-foreground">{{ release.version }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Tag</span>
                <span class="font-mono text-sm text-muted-foreground">{{ release.tag }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Channel</span>
                <span class="text-sm capitalize text-muted-foreground">{{ release.channel }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Published</span>
                <span class="text-sm text-muted-foreground">{{ formattedPublishedDate }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Created</span>
                <span class="text-sm text-muted-foreground">{{ formattedCreatedDate }}</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Release Notes</CardTitle>
              <CardDescription>What's new in this release</CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="release.notes" class="prose prose-sm dark:prose-invert max-w-none">
                <p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ release.notes }}</p>
              </div>
              <p v-else class="text-sm text-muted-foreground italic">
                No release notes available.
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Artifacts (placeholder for future implementation) -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Package class="h-5 w-5" />
              Artifacts
            </CardTitle>
            <CardDescription>Download artifacts for this release</CardDescription>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground">
              Artifacts management coming soon...
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
