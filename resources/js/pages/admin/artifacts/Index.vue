<script setup lang="ts">
import { computed, h, onMounted, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import artifacts from '@/routes/admin/artifacts'
import type { BreadcrumbItem } from '@/types'
import { DataTable, TableFilters, type Column, type FilterConfig } from '@/components/data-table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { MoreHorizontal, Download, Eye, FileArchive, ShieldCheck } from 'lucide-vue-next'
import { useTableData } from '@/composables/useTableData'
import type { Artifact, FilterParams } from '@/types/api'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Artifacts',
    href: artifacts.index().url,
  },
]

// Fetch data
const {
  data: artifactsData,
  meta,
  isLoading,
  fetchData,
  updateFilters,
  goToPage,
  clearFilters,
} = useTableData<Artifact>('/api/artifacts')

// Filters
const filterParams = ref<FilterParams>({})

const filterConfigs: FilterConfig[] = [
  {
    key: 'search',
    type: 'text',
    label: 'Search',
    placeholder: 'Search filenames...',
  },
  {
    key: 'platform',
    type: 'select',
    label: 'Platform',
    options: [
      { label: 'Darwin ARM64', value: 'darwin-aarch64' },
      { label: 'Darwin x86_64', value: 'darwin-x86_64' },
      { label: 'Windows x86_64', value: 'windows-x86_64' },
      { label: 'Linux x86_64', value: 'linux-x86_64' },
    ],
  },
  {
    key: 'source',
    type: 'select',
    label: 'Source',
    options: [
      { label: 'GitHub', value: 'github' },
      { label: 'R2', value: 'r2' },
      { label: 'S3', value: 's3' },
    ],
  },
  {
    key: 'notarized',
    type: 'select',
    label: 'Notarized',
    options: [{ label: 'Notarized Only', value: 'true' }],
  },
]

// Helper to format file size
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// Column definitions
const columns: Column<Artifact>[] = [
  {
    key: 'filename',
    label: 'Filename',
    headerClass: 'w-[200px]',
    render: (row: Artifact) => {
      return h('div', { class: 'flex items-center gap-2' }, [
        h(FileArchive, { class: 'h-4 w-4 text-muted-foreground flex-shrink-0' }),
        h('span', { class: 'font-mono text-xs truncate' }, row.filename),
      ])
    },
  },
  {
    key: 'platform',
    label: 'Platform',
    headerClass: 'w-[140px]',
    render: (row: Artifact) => {
      return h(
        Badge,
        { variant: 'outline', class: 'font-mono text-xs' },
        { default: () => row.platform }
      )
    },
  },
  {
    key: 'source',
    label: 'Source',
    headerClass: 'w-[100px]',
    render: (row: Artifact) => {
      const variantMap: Record<string, 'default' | 'secondary' | 'outline'> = {
        github: 'default',
        r2: 'secondary',
        s3: 'outline',
      }
      return h(
        Badge,
        { variant: variantMap[row.source] || 'outline', class: 'uppercase' },
        { default: () => row.source }
      )
    },
  },
  {
    key: 'size',
    label: 'Size',
    headerClass: 'w-[100px]',
    render: (row: Artifact) => {
      return h(
        'div',
        { class: 'text-sm text-muted-foreground' },
        formatFileSize(row.size)
      )
    },
  },
  {
    key: 'notarized',
    label: 'Notarized',
    headerClass: 'w-[80px] text-center',
    class: 'text-center',
    render: (row: Artifact) => {
      return row.notarized
        ? h(ShieldCheck, { class: 'h-4 w-4 text-green-600 dark:text-green-500 inline' })
        : h('span', { class: 'text-muted-foreground' }, '—')
    },
  },
  {
    key: 'release_id',
    label: 'Release',
    headerClass: 'w-[100px]',
    render: (row: Artifact) => {
      return h(
        'div',
        { class: 'font-mono text-xs text-muted-foreground truncate' },
        row.release_id.substring(0, 8)
      )
    },
  },
  {
    key: 'created_at',
    label: 'Created',
    headerClass: 'w-[140px]',
    render: (row: Artifact) => {
      const date = new Date(row.created_at)
      return h(
        'time',
        {
          datetime: row.created_at,
          class: 'text-sm text-muted-foreground',
        },
        date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        })
      )
    },
  },
  {
    key: 'actions',
    label: '',
    headerClass: 'w-[50px]',
    render: (row: Artifact) => {
      return h(
        DropdownMenu,
        {},
        {
          default: () => [
            h(
              DropdownMenuTrigger,
              { asChild: true },
              {
                default: () =>
                  h(
                    Button,
                    { variant: 'ghost', size: 'icon', class: 'h-8 w-8' },
                    {
                      default: () => [
                        h('span', { class: 'sr-only' }, 'Open menu'),
                        h(MoreHorizontal, { class: 'h-4 w-4' }),
                      ],
                    }
                  ),
              }
            ),
            h(
              DropdownMenuContent,
              { align: 'end' },
              {
                default: () => [
                  h(DropdownMenuLabel, {}, { default: () => 'Actions' }),
                  h(
                    DropdownMenuItem,
                    {
                      onClick: () => viewArtifact(row),
                    },
                    {
                      default: () => [
                        h(Eye, { class: 'mr-2 h-4 w-4' }),
                        'View Details',
                      ],
                    }
                  ),
                  h(
                    DropdownMenuItem,
                    {
                      onClick: () => downloadArtifact(row),
                    },
                    {
                      default: () => [
                        h(Download, { class: 'mr-2 h-4 w-4' }),
                        'Download',
                      ],
                    }
                  ),
                ],
              }
            ),
          ],
        }
      )
    },
  },
]

// Actions
const viewArtifact = (artifact: Artifact): void => {
  router.visit(`/admin/artifacts/${artifact.id}`)
}

const downloadArtifact = (artifact: Artifact): void => {
  window.open(artifact.url, '_blank')
}

const handleFilterApply = (): void => {
  updateFilters(filterParams.value)
}

const handleFilterClear = (): void => {
  filterParams.value = {}
  clearFilters()
}

const handlePageChange = (page: number): void => {
  goToPage(page)
}

// Fetch data on mount
onMounted(() => {
  fetchData()
})
</script>

<template>
  <Head title="Artifacts" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
          <h3 class="text-2xl font-semibold tracking-tight">Artifacts</h3>
          <p class="text-sm text-muted-foreground">
            Manage build artifacts for different platforms and releases.
          </p>
        </div>

        <div class="flex flex-col gap-4">
          <TableFilters
            v-model="filterParams"
            :filters="filterConfigs"
            @apply="handleFilterApply"
            @clear="handleFilterClear"
          />

          <DataTable
            :columns="columns"
            :data="artifactsData"
            :meta="meta"
            :is-loading="isLoading"
            empty-message="No artifacts found."
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
