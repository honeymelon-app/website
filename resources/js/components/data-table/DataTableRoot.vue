<script setup lang="ts" generic="TData">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    FlexRender,
    type ColumnDef,
    type Table as TanStackTable,
} from '@tanstack/vue-table';

defineProps<{
    table: TanStackTable<TData>;
    columns: ColumnDef<TData, unknown>[];
    emptyMessage?: string;
}>();
</script>

<template>
    <div class="overflow-x-auto rounded-md border">
        <Table>
            <TableHeader>
                <TableRow
                    v-for="headerGroup in table.getHeaderGroups()"
                    :key="headerGroup.id"
                >
                    <TableHead
                        v-for="header in headerGroup.headers"
                        :key="header.id"
                        :style="{
                            width:
                                header.getSize() !== 150
                                    ? `${header.getSize()}px`
                                    : undefined,
                        }"
                    >
                        <FlexRender
                            v-if="!header.isPlaceholder"
                            :render="header.column.columnDef.header"
                            :props="header.getContext()"
                        />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <TableRow
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        :data-state="row.getIsSelected() && 'selected'"
                    >
                        <TableCell
                            v-for="cell in row.getVisibleCells()"
                            :key="cell.id"
                        >
                            <FlexRender
                                :render="cell.column.columnDef.cell"
                                :props="cell.getContext()"
                            />
                        </TableCell>
                    </TableRow>
                </template>
                <TableRow v-else>
                    <TableCell
                        :colspan="columns.length"
                        class="h-24 text-center"
                    >
                        <div class="text-muted-foreground">
                            <slot name="empty">
                                {{ emptyMessage || 'No results found.' }}
                            </slot>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
