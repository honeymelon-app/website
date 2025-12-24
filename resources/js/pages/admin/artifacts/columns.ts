import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { formatDate, formatFileSize } from '@/lib/formatters';
import { getSourceVariant } from '@/lib/variants';
import releasesRoute from '@/routes/admin/releases';
import type { Artifact } from '@/types/resources';
import { router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import {
    AlertTriangle,
    ArrowUpDown,
    Cloud,
    Download,
    Eye,
    FileArchive,
    Github,
    MoreHorizontal,
    ShieldCheck,
    Trash2,
    XCircle,
} from 'lucide-vue-next';
import { h } from 'vue';

interface StorageStatus {
    synced: boolean;
    type: 'github' | 'r2' | 'missing_path' | 'not_found' | 'error';
    message: string;
    storage_size?: number;
    size_match?: boolean;
}

export interface ArtifactWithSync extends Artifact {
    storage_status: StorageStatus;
}

// Define an interface for the actions context (passed via meta)
interface ColumnActions {
    viewArtifact: (artifact: ArtifactWithSync) => void;
    downloadArtifact: (artifact: ArtifactWithSync) => void;
    confirmDelete: (artifact: ArtifactWithSync) => void;
}

export const columns: ColumnDef<ArtifactWithSync, ColumnActions>[] = [
    {
        id: 'select',
        header: ({ table }) =>
            h(Checkbox, {
                modelValue:
                    table.getIsAllPageRowsSelected() ||
                    (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                    table.toggleAllPageRowsSelected(!!value),
                ariaLabel: 'Select all',
                class: 'translate-y-0.5',
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                modelValue: row.getIsSelected(),
                'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                    row.toggleSelected(!!value),
                ariaLabel: 'Select row',
                class: 'translate-y-0.5',
            }),
        enableSorting: false,
        enableHiding: false,
        size: 40,
    },
    {
        accessorKey: 'filename',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Filename', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            const filename = row.getValue('filename') as string;
            return h('div', { class: 'flex items-center gap-2' }, [
                h(FileArchive, {
                    class: 'h-4 w-4 text-muted-foreground flex-shrink-0',
                }),
                h(
                    'span',
                    { class: 'font-mono text-xs truncate max-w-[200px]' },
                    filename || 'N/A',
                ),
            ]);
        },
    },
    {
        accessorKey: 'platform',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Platform', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) =>
            h(Badge, { variant: 'outline', class: 'font-mono text-xs' }, () =>
                row.getValue('platform'),
            ),
    },
    {
        accessorKey: 'source',
        header: 'Source',
        cell: ({ row }) => {
            const source = row.getValue('source') as string;
            return h(
                Badge,
                { variant: getSourceVariant(source), class: 'uppercase' },
                () => source,
            );
        },
    },
    {
        accessorKey: 'storage_status',
        header: 'Storage',
        cell: ({ row }) => {
            const status = row.original.storage_status;
            const iconClass = 'h-4 w-4';

            let icon;
            let colorClass;
            let tooltipText = status.message;

            if (status.synced) {
                if (status.type === 'github') {
                    icon = h(Github, { class: iconClass });
                    colorClass = 'text-foreground';
                } else {
                    icon = h(Cloud, { class: iconClass });
                    colorClass = 'text-green-600 dark:text-green-500';
                    if (status.size_match === false) {
                        icon = h(AlertTriangle, { class: iconClass });
                        colorClass = 'text-yellow-600 dark:text-yellow-500';
                        tooltipText = 'Size mismatch between DB and R2';
                    }
                }
            } else {
                icon = h(XCircle, { class: iconClass });
                colorClass = 'text-red-600 dark:text-red-500';
            }

            return h(
                TooltipProvider,
                {},
                {
                    default: () =>
                        h(
                            Tooltip,
                            {},
                            {
                                default: () => [
                                    h(
                                        TooltipTrigger,
                                        { asChild: true },
                                        {
                                            default: () =>
                                                h(
                                                    'div',
                                                    {
                                                        class: `flex items-center gap-1.5 ${colorClass}`,
                                                    },
                                                    [
                                                        icon,
                                                        h(
                                                            'span',
                                                            {
                                                                class: 'text-xs',
                                                            },
                                                            status.synced
                                                                ? 'Synced'
                                                                : 'Missing',
                                                        ),
                                                    ],
                                                ),
                                        },
                                    ),
                                    h(
                                        TooltipContent,
                                        {},
                                        { default: () => tooltipText },
                                    ),
                                ],
                            },
                        ),
                },
            );
        },
    },
    {
        accessorKey: 'size',
        header: 'Size',
        cell: ({ row }) =>
            h(
                'span',
                { class: 'text-sm text-muted-foreground' },
                formatFileSize(row.getValue('size')),
            ),
    },
    {
        accessorKey: 'notarized',
        header: 'Notarized',
        cell: ({ row }) => {
            const notarized = row.getValue('notarized') as boolean;
            return notarized
                ? h(ShieldCheck, {
                      class: 'h-4 w-4 text-green-600 dark:text-green-500',
                  })
                : h('span', { class: 'text-muted-foreground' }, '—');
        },
    },
    {
        accessorKey: 'release',
        header: 'Release',
        cell: ({ row }) => {
            const artifact = row.original;
            const version = artifact.release?.version ?? artifact.release_id;

            if (!version || !artifact.release_id) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    'N/A',
                );
            }

            return h(
                Button,
                {
                    variant: 'link',
                    class: 'px-0 text-xs font-mono text-muted-foreground',
                    onClick: () =>
                        router.visit(
                            releasesRoute.show(artifact.release_id!).url,
                        ),
                },
                { default: () => version },
            );
        },
    },
    {
        accessorKey: 'sha256',
        header: 'SHA256',
        cell: ({ row }) => {
            const sha256 = row.getValue('sha256') as string | null;

            if (!sha256) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    '—',
                );
            }

            const shortHash = `${sha256.substring(0, 10)}…`;

            return h(
                TooltipProvider,
                {},
                {
                    default: () =>
                        h(
                            Tooltip,
                            {},
                            {
                                default: () => [
                                    h(
                                        TooltipTrigger,
                                        { asChild: true },
                                        {
                                            default: () =>
                                                h(
                                                    'span',
                                                    {
                                                        class: 'font-mono text-xs text-muted-foreground',
                                                    },
                                                    shortHash,
                                                ),
                                        },
                                    ),
                                    h(
                                        TooltipContent,
                                        {},
                                        { default: () => sha256 },
                                    ),
                                ],
                            },
                        ),
                },
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Created', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            const date = row.getValue('created_at') as string;
            return h(
                'time',
                { datetime: date, class: 'text-sm text-muted-foreground' },
                formatDate(date),
            );
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row, table }) => {
            const artifact = row.original;
            const meta = table.options.meta as ColumnActions | undefined;

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
                                        {
                                            variant: 'ghost',
                                            size: 'icon',
                                            class: 'h-8 w-8',
                                        },
                                        {
                                            default: () => [
                                                h(
                                                    'span',
                                                    { class: 'sr-only' },
                                                    'Open menu',
                                                ),
                                                h(MoreHorizontal, {
                                                    class: 'h-4 w-4',
                                                }),
                                            ],
                                        },
                                    ),
                            },
                        ),
                        h(
                            DropdownMenuContent,
                            { align: 'end' },
                            {
                                default: () => [
                                    h(
                                        DropdownMenuLabel,
                                        {},
                                        { default: () => 'Actions' },
                                    ),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () =>
                                                meta?.viewArtifact(artifact),
                                        },
                                        {
                                            default: () => [
                                                h(Eye, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'View Details',
                                            ],
                                        },
                                    ),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () =>
                                                meta?.downloadArtifact(
                                                    artifact,
                                                ),
                                        },
                                        {
                                            default: () => [
                                                h(Download, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Download',
                                            ],
                                        },
                                    ),
                                    h(DropdownMenuSeparator),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () =>
                                                meta?.confirmDelete(artifact),
                                            class: 'text-destructive focus:text-destructive',
                                        },
                                        {
                                            default: () => [
                                                h(Trash2, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Delete',
                                            ],
                                        },
                                    ),
                                ],
                            },
                        ),
                    ],
                },
            );
        },
    },
];
