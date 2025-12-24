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
import { formatDate, truncateId } from '@/lib/formatters';
import { getStatusVariant } from '@/lib/variants';
import type { License } from '@/types/resources';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown, Eye, MoreHorizontal, ShieldOff } from 'lucide-vue-next';
import { h } from 'vue';

// Define an interface for the actions context (passed via meta)
interface ColumnActions {
    viewLicense: (license: License) => void;
    revokeLicense: (license: License) => void;
}

export const columns: ColumnDef<License, ColumnActions>[] = [
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
        accessorKey: 'id',
        header: 'License ID',
        cell: ({ row }) =>
            h(
                'span',
                { class: 'font-mono text-sm text-muted-foreground' },
                truncateId(row.getValue('id')),
            ),
    },
    {
        accessorKey: 'status',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Status', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            const status = row.getValue('status') as string;
            return h(
                Badge,
                { variant: getStatusVariant(status), class: 'capitalize' },
                () => status,
            );
        },
    },
    {
        accessorKey: 'max_major_version',
        header: 'Max Version',
        cell: ({ row }) => {
            const version = row.getValue('max_major_version') as number;
            return h(
                'span',
                { class: 'text-sm text-muted-foreground' },
                version === 999 ? 'Lifetime' : `v${version}.x`,
            );
        },
    },
    {
        accessorKey: 'issued_at',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Issued', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            const date = row.getValue('issued_at') as string | null;
            return date
                ? h('span', { class: 'text-sm' }, formatDate(date))
                : h(
                      'span',
                      { class: 'text-sm text-muted-foreground' },
                      'Not issued',
                  );
        },
    },
    {
        accessorKey: 'activation_count',
        header: 'Activations',
        cell: ({ row }) => {
            const count = (row.getValue('activation_count') as number) ?? 0;
            return h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs' },
                () => `${count} device${count === 1 ? '' : 's'}`,
            );
        },
    },
    {
        accessorKey: 'device_id',
        header: 'Device ID',
        cell: ({ row }) => {
            const deviceId = row.getValue('device_id') as string | null;
            return deviceId
                ? h(
                      'span',
                      {
                          class: 'font-mono text-xs text-muted-foreground truncate max-w-[160px] block',
                      },
                      truncateId(deviceId),
                  )
                : h(
                      'span',
                      { class: 'text-sm text-muted-foreground' },
                      'Not activated',
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
            const license = row.original;
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
                                default: () =>
                                    [
                                        h(
                                            DropdownMenuLabel,
                                            {},
                                            { default: () => 'Actions' },
                                        ),
                                        h(
                                            DropdownMenuItem,
                                            {
                                                onClick: () =>
                                                    meta?.viewLicense(license),
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
                                        license.status === 'active'
                                            ? h(DropdownMenuSeparator)
                                            : null,
                                        license.status === 'active'
                                            ? h(
                                                  DropdownMenuItem,
                                                  {
                                                      onClick: () =>
                                                          meta?.revokeLicense(
                                                              license,
                                                          ),
                                                      class: 'text-destructive focus:text-destructive',
                                                  },
                                                  {
                                                      default: () => [
                                                          h(ShieldOff, {
                                                              class: 'mr-2 h-4 w-4',
                                                          }),
                                                          'Revoke License',
                                                      ],
                                                  },
                                              )
                                            : null,
                                    ].filter(Boolean),
                            },
                        ),
                    ],
                },
            );
        },
    },
];
