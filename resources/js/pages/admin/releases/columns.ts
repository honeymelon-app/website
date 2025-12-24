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
import { formatDate } from '@/lib/formatters';
import { getChannelVariant } from '@/lib/variants';
import releasesRoute from '@/routes/admin/releases';
import type { Release } from '@/types/resources';
import { router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import {
    ArrowUpDown,
    Eye,
    MoreHorizontal,
    PackageSearch,
    Rocket,
} from 'lucide-vue-next';
import { h } from 'vue';

export const columns: ColumnDef<Release>[] = [
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
        accessorKey: 'version',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Version', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) =>
            h(
                'span',
                { class: 'font-mono font-medium' },
                row.getValue('version'),
            ),
    },
    {
        accessorKey: 'tag',
        header: 'Tag',
        cell: ({ row }) =>
            h(
                'span',
                { class: 'font-mono text-xs text-muted-foreground' },
                row.getValue('tag'),
            ),
    },
    {
        accessorKey: 'commit_hash',
        header: 'Commit',
        cell: ({ row }) => {
            const hash = row.getValue('commit_hash') as string | null;
            return h(
                'span',
                { class: 'font-mono text-xs text-muted-foreground' },
                hash?.slice(0, 7) ?? '—',
            );
        },
    },
    {
        accessorKey: 'channel',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Channel', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            const channel = row.getValue('channel') as string;
            return h(
                Badge,
                { variant: getChannelVariant(channel), class: 'capitalize' },
                () => channel,
            );
        },
    },
    {
        accessorKey: 'major',
        header: 'Major',
        cell: ({ row }) => {
            const isMajor = row.getValue('major') as boolean;
            return isMajor
                ? h(
                      Badge,
                      { variant: 'destructive', class: 'text-xs' },
                      () => 'Major',
                  )
                : h('span', { class: 'text-muted-foreground' }, '—');
        },
    },
    {
        accessorKey: 'artifacts_count',
        header: 'Artifacts',
        cell: ({ row }) => {
            const count = (row.getValue('artifacts_count') as number) ?? 0;
            return h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs' },
                () => `${count} file${count === 1 ? '' : 's'}`,
            );
        },
    },
    {
        accessorKey: 'published_at',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Published', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            const date = row.getValue('published_at') as string | null;
            return date
                ? h('span', { class: 'text-sm' }, formatDate(date))
                : h(
                      'span',
                      { class: 'text-sm text-muted-foreground' },
                      'Not published',
                  );
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const release = row.original;
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
                                                router.visit(
                                                    releasesRoute.show(
                                                        release.id,
                                                    ).url,
                                                ),
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
                                                router.visit(
                                                    `${releasesRoute.show(release.id).url}#artifacts`,
                                                ),
                                        },
                                        {
                                            default: () => [
                                                h(PackageSearch, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'View Artifacts',
                                            ],
                                        },
                                    ),
                                    h(DropdownMenuSeparator),
                                    h(
                                        DropdownMenuItem,
                                        {},
                                        {
                                            default: () => [
                                                h(Rocket, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Publish to Channel',
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
