// Core Components
export { default as DataTable } from './core/DataTable.vue';
export { default as DataTablePagination } from './core/DataTablePagination.vue';
export { default as DataTableRowActions } from './core/DataTableRowActions.vue';
export { default as DataTableToolbar } from './core/DataTableToolbar.vue';
export { default as DateDisplayCell } from './core/DateDisplayCell.vue';
export { default as MainDataCell } from './core/MainDataCell.vue';

// Feature Components
export { default as ExportDialog } from './features/ExportDialog.vue';

// Types
export type {
    BulkAction,
    DefaultRowActionsConfig,
    ExportConfig,
    ExportFormat,
    ExportOptions,
    FilterConfig,
    Identifiable,
    PaginationInfo,
    PaginationMeta,
    RowAction,
    TableProps,
    // Nuevos tipos para mejor configuración
    DeletionConfig,
    ToolbarConfig,
    PaginationConfig,
} from './types/datatable.types';

