<script setup lang="ts">
import { computed, h } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import updates from '@/routes/admin/updates'
import releases from '@/routes/admin/releases'
import type { BreadcrumbItem } from '@/types'
import type { Update } from '@/types/api'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, CheckCircle, Code } from 'lucide-vue-next'

interface Props {
  update: Update
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Updates',
    href: updates.index().url,
  },
  {
    title: props.update.version,
    href: updates.show(props.update.id).url,
  },
]

const formattedPublishedDate = computed(() => {
  const date = new Date(props.update.published_at)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
})

const formattedCreatedDate = computed(() => {
  const date = new Date(props.update.created_at)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

const formattedManifest = computed(() => {
  return JSON.stringify(props.update.manifest, null, 2)
})

const goBack = () => {
  router.visit(updates.index().url)
}

const viewRelease = () => {
  if (props.update.release_id) {
    router.visit(releases.show(props.update.release_id).url)
  }
}
</script>

<template>
  <Head :title="`Update ${update.version}`" />

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
                Update {{ update.version }}
              </h3>
            </div>
            <p class="text-sm text-muted-foreground">
              View update manifest and metadata.
            </p>
          </div>
          <div class="flex gap-2">
            <Badge :variant="update.channel === 'stable' ? 'default' : 'secondary'" class="capitalize">
              {{ update.channel }}
            </Badge>
            <Badge v-if="update.is_latest" variant="outline" class="gap-1">
              <CheckCircle class="h-3 w-3" />
              Latest
            </Badge>
          </div>
        </div>

        <!-- Update Info -->
        <div class="grid gap-4 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Update Information</CardTitle>
              <CardDescription>Basic details about this update</CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col gap-4">
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Version</span>
                <span class="font-mono text-sm text-muted-foreground">{{ update.version }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Channel</span>
                <span class="text-sm capitalize text-muted-foreground">{{ update.channel }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Is Latest</span>
                <div class="flex items-center gap-2">
                  <CheckCircle v-if="update.is_latest" class="h-4 w-4 text-green-600 dark:text-green-500" />
                  <span class="text-sm text-muted-foreground">
                    {{ update.is_latest ? 'Yes' : 'No' }}
                  </span>
                </div>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-sm font-medium">Release ID</span>
                <Button
                  v-if="update.release_id"
                  variant="link"
                  class="h-auto justify-start p-0 font-mono text-sm"
                  @click="viewRelease"
                >
                  {{ update.release_id }}
                </Button>
                <span v-else class="font-mono text-sm text-muted-foreground">—</span>
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
              <CardTitle class="flex items-center gap-2">
                <Code class="h-5 w-5" />
                Manifest
              </CardTitle>
              <CardDescription>Update manifest JSON</CardDescription>
            </CardHeader>
            <CardContent>
              <pre class="overflow-x-auto rounded-md bg-muted p-4 text-xs"><code>{{ formattedManifest }}</code></pre>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
