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
import { formatDate, getAvatarUrl, truncateId } from '@/lib/formatters';
import ordersRoute from '@/routes/admin/orders';
import type { Order } from '@/types/resources';
import { router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import {
    ArrowUpDown,
    BadgeCheck,
    CircleDollarSign,
    Citrus,
    Eye,
    KeyRound,
    MoreHorizontal,
    RotateCcw,
} from 'lucide-vue-next';
import { h } from 'vue';

export const columns: ColumnDef<Order>[] = [
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
        accessorKey: 'email',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Customer', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            const email = row.getValue('email') as string;
            const provider = row.original.provider;
            const isStripe = provider === 'stripe';
            const ProviderIcon = isStripe ? CircleDollarSign : Citrus;

            return h('div', { class: 'flex items-center gap-3' }, [
                h('div', { class: 'relative' }, [
                    h('img', {
                        src: getAvatarUrl(email, 36),
                        alt: email,
                        class: 'h-9 w-9 rounded-full',
                    }),
                    h(
                        'span',
                        {
                            class: `absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full ring-2 ring-background ${
                                isStripe
                                    ? 'bg-violet-500 text-white'
                                    : 'bg-amber-500 text-white'
                            }`,
                        },
                        h(ProviderIcon, { class: 'h-2.5 w-2.5' }),
                    ),
                ]),
                h('div', { class: 'min-w-0 flex-1' }, [
                    h(
                        'p',
                        { class: 'truncate text-sm font-medium' },
                        email.split('@')[0],
                    ),
                    h(
                        'p',
                        { class: 'truncate text-xs text-muted-foreground' },
                        `@${email.split('@')[1]}`,
                    ),
                ]),
            ]);
        },
    },
    {
        accessorKey: 'amount_cents',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Amount', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-sm font-medium' },
                row.original.formatted_amount,
            );
        },
    },
    {
        id: 'status',
        header: 'Status',
        cell: ({ row }) => {
            const order = row.original;
            const isRefunded = order.is_refunded;
            const Icon = isRefunded ? RotateCcw : BadgeCheck;

            return h(
                'span',
                {
                    class: `inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ${
                        isRefunded
                            ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400'
                    }`,
                },
                [
                    h(Icon, { class: 'h-3.5 w-3.5' }),
                    isRefunded ? 'Refunded' : 'Paid',
                ],
            );
        },
        enableSorting: false,
    },
    {
        accessorKey: 'license_id',
        header: 'License',
        cell: ({ row }) => {
            const licenseId = row.getValue('license_id') as string | null;

            if (!licenseId) {
                return h(
                    'span',
                    { class: 'text-muted-foreground/60 text-sm' },
                    '—',
                );
            }

            return h(TooltipProvider, {}, () =>
                h(
                    Tooltip,
                    {},
                    {
                        default: () => [
                            h(TooltipTrigger, { asChild: true }, () =>
                                h(
                                    'span',
                                    {
                                        class: 'inline-flex cursor-default items-center gap-1.5 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400 dark:ring-emerald-500/30',
                                    },
                                    [
                                        h(KeyRound, {
                                            class: 'h-3 w-3',
                                        }),
                                        truncateId(licenseId),
                                    ],
                                ),
                            ),
                            h(TooltipContent, {}, () => licenseId),
                        ],
                    },
                ),
            );
        },
        enableSorting: false,
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    class: '-ml-4',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Created', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            const createdAt = row.getValue('created_at') as string;
            return h(
                'time',
                {
                    datetime: createdAt,
                    class: 'text-sm text-muted-foreground',
                },
                formatDate(createdAt),
            );
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const order = row.original;

            const viewOrder = () => {
                router.visit(ordersRoute.show(order.id).url);
            };

            const refundOrder = () => {
                if (!order.can_be_refunded) {
                    return;
                }

                const confirmed = confirm(
                    `Refund order ${truncateId(order.id)}? This will revoke the associated license.`,
                );

                if (!confirmed) {
                    return;
                }

                const reason =
                    window.prompt('Refund reason (optional)')?.trim() ?? '';

                router.post(
                    ordersRoute.refund(order.id).url,
                    { reason: reason || null },
                    { preserveScroll: true },
                );
            };

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
                                    h(DropdownMenuSeparator),
                                    h(
                                        DropdownMenuItem,
                                        { onClick: viewOrder },
                                        {
                                            default: () => [
                                                h(Eye, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'View Details',
                                            ],
                                        },
                                    ),
                                    order.can_be_refunded
                                        ? h(
                                              DropdownMenuItem,
                                              {
                                                  onClick: refundOrder,
                                                  class: 'text-destructive focus:text-destructive',
                                              },
                                              {
                                                  default: () => [
                                                      h(RotateCcw, {
                                                          class: 'mr-2 h-4 w-4',
                                                      }),
                                                      'Refund Order',
                                                  ],
                                              },
                                          )
                                        : null,
                                ],
                            },
                        ),
                    ],
                },
            );
        },
    },
];
