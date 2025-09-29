<script setup lang="ts" generic="TData extends Identifiable, TApiResponse">
import { useMutation, useQuery } from '@tanstack/vue-query';
import {
    type ColumnDef,
    type ColumnFiltersState,
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    type PaginationState,
    Row,
    type RowSelectionState,
    type SortingState,
    useVueTable,
    type VisibilityState
} from '@tanstack/vue-table';
import axios from 'axios';
import { computed, h, ref, watch } from 'vue';
import type { BulkAction, ExportFormat, Identifiable, PaginationInfo, PaginationMeta, TableProps } from '../index';
import { DataTablePagination, DataTableToolbar, ExportDialog } from '../index';

import { Sheet } from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';

import { toast } from 'vue-sonner';
import DateDisplayCell from './DateDisplayCell.vue';

const props = withDefaults(defineProps<TableProps<TData, TApiResponse>>(), {
    /**
     * @description The endpoint to fetch the data from.
     */
    endpoint: '',
    /**
     * @description The columns to display in the table.
     */
    columns: () => [],
    /**
     * @description The function to transform the data from the API.
     */
    transformFn: undefined,
    /**
     * @description The module ID.
     */
    moduleId: '',
    /**
     * @description The initial page size.
     */
    initialPageSize: 15,
    /**
     * @description The placeholder for the global search input.
     */
    searchPlaceholder: 'Buscar...',
    /**
     * @description Whether to enable row selection.
     */
    enableRowSelection: true,
    /**
     * @description The row actions to display in the table.
     */
    rowActions: () => [],
    /**
     * @description The deleted row actions to display in the table.
     */
    deletedRowActions: () => [],
    /**
     * @description The default row actions config.
     */
    defaultRowActionsConfig: () => ({}),
    /**
     * @description The bulk actions to display in the table.
     */
    bulkActions: () => [],
    /**
     * @description The custom filters to display in the table.
     */
    customFilters: () => [],
    /**
     * @description The initial filters to display in the table.
     */
    initialFilters: () => ({}),
    /**
     * @description The deleted mode to display in the table.
     */
    deletedMode: false,
    /**
     * @description The class to display in the table.
     */
    class: '',
    /**
     * @description The export config to display in the table.
     */
    exportConfig: undefined,
    /**
     * @description Whether to enable the deleted mode toggle.
     */
    enableDeletedModeToggle: false,


    density: 'normal',
    showToolbar: true,
    showPagination: true,
    showBulkActions: true,
    compactMode: false,
    rowHeight: 'md',
});

const emit = defineEmits<{
    (e: 'update:appliedFilters', value: Record<string, string>): void;
    (e: 'clear-all-filters'): void;
    (e: 'export', format: ExportFormat): void;
    (e: 'update:deletedMode', value: boolean): void;
}>();

const internalDeletedMode = ref(props.deletedMode);

watch(
    () => props.deletedMode,
    (newValue) => {
        internalDeletedMode.value = newValue;
    }
);

const tableColumns = computed<ColumnDef<TData, any>[]>(() => {
    let allColumns = [...props.columns];

    // Agregar columna de selección si está habilitada
    if (props.enableRowSelection) {
        const selectionColumn: ColumnDef<TData, any> = {
            id: 'select',
            header: ({ table }) =>
                h(Checkbox, {
                    modelValue:
                        table.getIsAllPageRowsSelected() ||
                        (table.getIsSomePageRowsSelected() && 'indeterminate'),
                    'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                        table.toggleAllPageRowsSelected(!!value),
                    ariaLabel: 'Seleccionar todas las filas',
                    class: 'translate-y-0.5'
                }),
            cell: ({ row }) =>
                h(Checkbox, {
                    modelValue: row.getIsSelected(),
                    disabled: !row.getCanSelect(),
                    'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                        row.toggleSelected(!!value),
                    ariaLabel: 'Seleccionar fila',
                    class: 'translate-y-0.5'
                }),
            enableSorting: false,
            enableHiding: false,
            size: 40
        };

        allColumns = [selectionColumn, ...allColumns];
    }


    // Agregar columna de fecha de eliminación si está en modo eliminación
    if (internalDeletedMode.value) {
        const deletedAtColumn: ColumnDef<TData, any> = {
            accessorKey: 'deleted_at',
            header: 'Fecha de Eliminación',
            cell: ({ getValue }) => {
                const value = getValue<string | null>();
                if (!value) return 'N/A';
                return h(DateDisplayCell, { value });
            },
        };

        const actionsColumnIndex = allColumns.findIndex((c) => c.id === 'actions');

        if (actionsColumnIndex > -1) {
            allColumns.splice(actionsColumnIndex, 0, deletedAtColumn);
        } else {
            allColumns = [...allColumns, deletedAtColumn];
        }
    }

    return allColumns;
});

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const appliedFilters = ref<Record<string, string>>({ ...props.initialFilters });
const rowSelection = ref<RowSelectionState>({});
const pagination = ref<PaginationState>({
    pageIndex: 0,
    pageSize: props.initialPageSize,
});

const previewSheetId = ref<string | number | null>(null);
const isPreviewSheetOpen = ref(false);
const isExportDialogOpen = ref(false);

const queryKey = computed(() => [
    props.moduleId,
    'data',
    props.endpoint,
    pagination.value.pageIndex,
    pagination.value.pageSize,
    appliedFilters.value,
    sorting.value,
    internalDeletedMode.value,
]);

const {
    data: queryResult,
    isLoading,
    isError,
    error: queryError,
    refetch,
} = useQuery({
    queryKey: queryKey,
    queryFn: async () => {
        const params: Record<string, any> = {
            page: {
                number: pagination.value.pageIndex + 1,
                size: pagination.value.pageSize,
            },
        };

        if (internalDeletedMode.value) params.filter = { trashed: 'only' };

        Object.entries(appliedFilters.value).forEach(([key, value]) => {
            if (value && value !== '') {
                params[`filter[${key}]`] = value;
            }
        });

        if (sorting.value.length > 0) {
            params.sort = sorting.value.map((s) => `${s.desc ? '-' : ''}${s.id}`).join(',');
        }
        const response = await axios.get<TApiResponse>(props.endpoint, { params });
        return props.transformFn ? props.transformFn(response.data) : response.data;
    },
});

const tableData = computed(() => {
    if (!queryResult.value) return [];
    const result = queryResult.value as { data: TData[]; meta: PaginationMeta };
    return result.data || [];
});

const paginationMeta = computed<PaginationMeta | null>(() => {
    if (!queryResult.value) return null;
    const result = queryResult.value as { data: TData[]; meta: PaginationMeta };
    return result.meta || null;
});

const paginationInfo = computed<PaginationInfo>(() => ({
    currentPage: paginationMeta.value?.current_page || pagination.value.pageIndex + 1,
    totalPages: paginationMeta.value?.last_page || 1,
    totalItems: paginationMeta.value?.total || 0,
    perPage: paginationMeta.value?.per_page || pagination.value.pageSize,
}));

const table = useVueTable({
    get data() {
        return tableData.value;
    },
    get columns() {
        return tableColumns.value;
    },
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get pagination() {
            return pagination.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
    },
    enableRowSelection: props.enableRowSelection,
    onSortingChange: (updater) => (sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater),
    onPaginationChange: (updater) => (pagination.value = typeof updater === 'function' ? updater(pagination.value) : updater),
    onRowSelectionChange: (updater) => (rowSelection.value = typeof updater === 'function' ? updater(rowSelection.value) : updater),
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    get pageCount() {
        const totalApiPages = paginationInfo.value.totalPages;
        return totalApiPages > 0 ? totalApiPages : -1;
    },
    meta: {
        refreshData: () => refetch(),
        openPreviewSheet: (id: string | number) => {
            previewSheetId.value = id;
            isPreviewSheetOpen.value = true;
        },
        customActionHandler: (eventName: string, rowData: TData) => {

        },
        rowActions: props.rowActions,
        deletedRowActions: props.deletedRowActions,
        isDeletedMode: internalDeletedMode,
    },
});

const bulkActionMutation = useMutation({
    mutationFn: async ({ endpoint, method = 'POST', selectedIds }: { endpoint: string; method?: string; selectedIds: (string | number)[] }) => {
        return axios.request({ url: endpoint, method, data: { ids: selectedIds } });
    },
    onSuccess: () => {
        toast.success(`Acción masiva completada.`);
        refetch();
        rowSelection.value = {};
    },
    onError: (error: any) => {
        toast.error(`Error en acción masiva: ${error.response?.data?.message || error.message}`);
    },
});

const executeBulkAction = (action: BulkAction<TData>) => {
    const selectedRowIds = table.getSelectedRowModel().rows.map((row) => row.original.id);
    if (selectedRowIds.length === 0) {
        toast.info('No hay filas seleccionadas.');
        return;
    }
    if (action.confirm && !confirm(action.confirmMessage || '¿Estás seguro?')) {
        return;
    }
    bulkActionMutation.mutate({
        endpoint: action.endpoint,
        method: action.method as string,
        selectedIds: selectedRowIds,
    });
};

const handleClearAllFilters = () => {
    appliedFilters.value = {};
};

const toggleDeletedMode = () => {
    internalDeletedMode.value = !internalDeletedMode.value;
    emit('update:deletedMode', internalDeletedMode.value);
};

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const slots = defineSlots<{
    'toolbar-actions': any;
    'empty-state': any;
}>();

const PreviewComponent = props.defaultRowActionsConfig?.previewComponent;

const getButtonDisabledState = (action: BulkAction<TData>, selectedRows: Row<TData>[]) => {
    const isActionDisabled = action.disabled ? action.disabled(selectedRows) : false;
    const isMutationPending = bulkActionMutation.isPending.value;
    return isActionDisabled || isMutationPending;
};

type ButtonVariant = 'primary' | 'secondary' | 'outline' | 'destructive' | 'ghost' | 'link';

const getButtonClasses = (variant: ButtonVariant = 'primary') => {
    const baseClasses = `
    inline-flex items-center justify-center whitespace-nowrap rounded-md
    text-sm font-medium ring-offset-background transition-colors
    focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2
    disabled:pointer-events-none disabled:opacity-50
    h-9 px-3`;

    const variantClasses: Record<ButtonVariant, string> = {
        primary: 'bg-primary text-primary-foreground hover:bg-primary/90',
        secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
        destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
        ghost: 'hover:bg-accent hover:text-accent-foreground',
        link: 'text-primary underline-offset-4 hover:underline h-auto px-0 py-0',
    };

    return `${baseClasses} ${variantClasses[variant]}`;
};

// Computed properties para clases dinámicas
const tableClasses = computed(() => {
    const baseClasses = 'overflow-hidden rounded-md border shadow-sm';
    const densityClasses = {
        compact: 'text-xs',
        normal: 'text-sm',
        comfortable: 'text-base',
    };
    return `${baseClasses} ${densityClasses[props.density]}`;
});

const cellClasses = computed(() => {
    const baseClasses = 'transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

    const densityClasses = {
        compact: 'px-2 py-1',
        normal: 'px-3 py-2.5',
        comfortable: 'px-4 py-3',
    };

    const heightClasses = {
        sm: 'h-8',
        md: 'h-10',
        lg: 'h-12',
    };

    return `${baseClasses} ${densityClasses[props.density]} ${heightClasses[props.rowHeight]}`;
});

const headerClasses = computed(() => {
    const baseClasses = 'font-medium text-muted-foreground transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

    const densityClasses = {
        compact: 'px-2 py-1.5',
        normal: 'px-3 py-2.5',
        comfortable: 'px-4 py-3',
    };

    return `${baseClasses} ${densityClasses[props.density]}`;
});

const containerClasses = computed(() => {
    const baseClasses = 'space-y-4';
    const compactClasses = props.compactMode ? 'space-y-2' : baseClasses;
    return compactClasses;
});

const handleExport = async (format: ExportFormat) => {
    if (!props.exportConfig) return;

    try {
        if (props.exportConfig.onExport) {
            await props.exportConfig.onExport(format);
            return;
        }

        const params: Record<string, any> = {
            format,
            ...appliedFilters.value,
        };

        if (internalDeletedMode.value) params.trashed = true;

        if (sorting.value.length > 0) {
            params.sort = sorting.value.map((s) => `${s.desc ? '-' : ''}${s.id}`).join(',');
        }

        const response = await axios.get(props.exportConfig.endpoint, {
            params,
            responseType: 'blob',
        });

        const filename = props.exportConfig.filename || `export-${new Date().toISOString()}.${format}`;
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        toast.success('Exportación completada');
    } catch (error) {
        console.error('Error exporting:', error);
        toast.error('Error al exportar los datos');
    }
};
</script>

<template>
    <div :class="containerClasses">
        <DataTableToolbar v-if="props.showToolbar" :table="table"
            :searchPlaceholder="props.searchPlaceholder"
            :customFilters="props.customFilters" :appliedFilters="appliedFilters" :exportConfig="props.exportConfig"
            :enableDeletedModeToggle="props.enableDeletedModeToggle" :deletedMode="internalDeletedMode"
            @update:appliedFilters="appliedFilters = $event"
            @clear-all-filters="handleClearAllFilters" @export="isExportDialogOpen = true"
            @toggle-deleted-mode="toggleDeletedMode">
            <template #toolbar-actions>
                <slot name="toolbar-actions"></slot>
            </template>
        </DataTableToolbar>

        <div v-if="props.showBulkActions && props.enableRowSelection && table.getSelectedRowModel().rows.length > 0 && props.bulkActions && props.bulkActions.length > 0"
            class="bg-muted flex items-center justify-between rounded-md p-3 shadow">
            <span class="text-muted-foreground text-sm">{{ table.getSelectedRowModel().rows.length }} fila(s)
                seleccionada(s)</span>
            <div class="space-x-2">
                <button v-for="action in props.bulkActions" :key="action.id" @click="executeBulkAction(action)"
                    :disabled="getButtonDisabledState(action, table.getSelectedRowModel().rows)"
                    :class="getButtonClasses(action.variant as ButtonVariant)">
                    <component v-if="action.icon && typeof action.icon !== 'string'" :is="action.icon"
                        class="mr-2 h-4 w-4" />
                    {{ action.label }}
                </button>
            </div>
        </div>

        <div :class="tableClasses">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" :colSpan="header.colSpan"
                            :style="{ width: header.getSize() !== 150 ? `${header.getSize()}px` : undefined }"
                            @click="header.column.getCanSort() ? header.column.getToggleSortingHandler()?.($event) : undefined"
                            :class="[
                                headerClasses,
                                { 'hover:bg-muted/50 cursor-pointer select-none': header.column.getCanSort() }
                            ]">
                            <div class="flex items-center gap-2">
                                <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                    :props="header.getContext()" />
                                <template v-if="header.column.getCanSort()">
                                    <template v-if="header.column.getIsSorted() === 'asc'">
                                        <ArrowUp class="h-4 w-4" />
                                    </template>
                                    <template v-else-if="header.column.getIsSorted() === 'desc'">
                                        <ArrowDown class="h-4 w-4" />
                                    </template>
                                </template>
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="isLoading">
                        <TableRow v-for="i in pagination.pageSize" :key="`skeleton-${i}`">
                            <TableCell v-for="column in props.columns"
                                :key="`skeleton-cell-${i}-${column.id || (column as any).accessorKey || Math.random()}`"
                                :class="cellClasses">
                                <Skeleton class="my-1 h-6 w-full" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined"
                            class="hover:bg-muted/50 data-[state=selected]:bg-primary/10">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" :class="cellClasses">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colSpan="table.getVisibleLeafColumns().length" class="h-24 text-center">
                                <slot name="empty-state">
                                    <div class="text-muted-foreground py-8">No hay resultados para mostrar.</div>
                                </slot>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <DataTablePagination v-if="props.showPagination" :table="table" />
        <div v-if="isError && queryError"
            class="rounded-md border border-red-600 bg-red-50 p-4 text-red-600 dark:bg-red-900/30 dark:text-red-300">
            Error al cargar los datos: {{ queryError?.message }}
        </div>

        <Sheet v-if="PreviewComponent" v-model:open="isPreviewSheetOpen">
            <component :is="PreviewComponent" :id="previewSheetId" />
        </Sheet>

        <ExportDialog v-if="props.exportConfig" :isOpen="isExportDialogOpen"
            @update:isOpen="isExportDialogOpen = $event" :formats="props.exportConfig.formats"
            :onExport="props.exportConfig.onExport || handleExport" />
    </div>
</template>
