import type { ColumnDef as TanStackColumnDef, Row, Table } from '@tanstack/vue-table';
import type { Component, Ref } from 'vue';

export interface Identifiable {
    id: string | number;
}

export type ExportFormat = 'csv' | 'xlsx' | 'pdf' | 'json';

export interface ExportConfig {
    formats: ExportFormat[];
    endpoint: string;
    filename: string;
    onExport: (format: ExportFormat) => Promise<void>;
    // Nuevas propiedades para mejor control
    enabled?: boolean;
    permission?: string | string[];
    showSelectedOnly?: boolean;
    customHeaders?: Record<string, string>;
    customParams?: Record<string, any>;
}

export interface ExportOptions {
    format: ExportFormat;
    selectedRows?: Row<any>[];
    filters?: Record<string, any>;
    customParams?: Record<string, any>;
}

export type TransformFn<TApiResponse, TData> = (jsonData: TApiResponse) => { data: TData[]; meta: PaginationMeta };

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    from?: number;
    to?: number;
}

export interface PaginationInfo {
    currentPage: number;
    totalPages: number;
    totalItems: number;
    perPage: number;
}

export interface TableProps<TData extends Identifiable, TApiResponse> {
    endpoint: string;
    columns: ColumnDef<TData, any>[];
    transformFn?: TransformFn<TApiResponse, TData>;
    moduleId: string;
    initialPageSize?: number;
    searchPlaceholder?: string;
    enableRowSelection?: boolean;
    rowActions?: RowAction<TData>[];
    deletedRowActions?: RowAction<TData>[];
    defaultRowActionsConfig?: DefaultRowActionsConfig<TData>;
    bulkActions?: BulkAction<TData>[];
    customFilters?: FilterConfig[];
    initialFilters?: Record<string, string>;
    deletedMode?: boolean;
    class?: string;
    exportConfig?: ExportConfig;
    enableDeletedModeToggle?: boolean;
    // Nuevas props para compactación
    density?: 'compact' | 'normal' | 'comfortable';
    showToolbar?: boolean;
    showPagination?: boolean;
    showBulkActions?: boolean;
    compactMode?: boolean;
    rowHeight?: 'sm' | 'md' | 'lg';
    // Nuevas props para mejor control
    showExportButton?: boolean;
    showDeletedModeToggle?: boolean;
    showFiltersToggle?: boolean;
    showGlobalSearch?: boolean;
    showBulkActionsToggle?: boolean;
}

export interface FilterConfig {
    key: string;
    label: string;
    type: 'text' | 'select' | 'multiselect' | 'date' | 'date-range';
    options?: Array<{ value: string; label: string }>;
    placeholder?: string;
    group?: 'basic' | 'advanced';
    enabled?: boolean;
    // Nuevas propiedades para mejor control
    defaultValue?: any;
    validation?: {
        required?: boolean;
        min?: number;
        max?: number;
        pattern?: string;
    };
    transform?: (value: any) => any;
    dependsOn?: string; // Para filtros dependientes
}

export interface RowAction<TData extends Identifiable> {
    label: string;
    icon?: string | Component;
    actionKind: 'event' | 'http' | 'function' | 'route';
    eventName?: string;
    routeName?: string;
    routeParams?: (row: TData) => Record<string, any>;
    endpoint?: string | ((row: TData) => string);
    httpMethod?: 'POST' | 'PUT' | 'DELETE' | 'GET';
    actionFn?: (row: TData, table: Table<TData>) => Promise<void> | void;
    permission?: string | string[];
    confirm?: boolean;
    confirmMessage?: string;
    feedback?: 'toast' | 'none';
    uiBehavior?: 'notifyRefresh' | 'refresh' | 'none';
    onSuccess?: (response?: any) => void;
    onError?: (error: any) => void;
    class?: string;
    disabled?: (row: TData) => boolean;
    order?: number;
    hasAccess?: (row: TData) => boolean;
    // Nuevas propiedades para mejor control
    loading?: boolean;
    tooltip?: string;
    badge?: string;
    badgeVariant?: 'default' | 'secondary' | 'destructive' | 'outline';
    showInDeletedMode?: boolean;
    showInNormalMode?: boolean;
}

export interface DefaultRowActionConfig {
    permission?: string | string[];
    enabled?: boolean;
    order?: number;
    onSuccess?: (response?: any) => void;
    onError?: (error: any) => void;
    // Nuevas propiedades
    confirm?: boolean;
    confirmMessage?: string;
    uiBehavior?: 'notifyRefresh' | 'refresh' | 'none';
    class?: string;
    tooltip?: string;
}

export interface DefaultRowActionsConfig<TData extends Identifiable> {
    normalMode?: {
        view?: DefaultRowActionConfig | boolean;
        edit?: DefaultRowActionConfig | boolean;
        delete?: DefaultRowActionConfig | boolean;
        preview?: DefaultRowActionConfig | boolean;
    };
    deletedMode?: {
        restore?: DefaultRowActionConfig | boolean;
        permanentlyDelete?: DefaultRowActionConfig | boolean;
    };
    viewRoute?: (row: TData) => string;
    viewRouteParams?: (row: TData) => Record<string, any>;
    editRoute?: (row: TData) => string;
    editRouteParams?: (row: TData) => Record<string, any>;
    deleteRoute?: (row: TData) => string;
    previewComponent?: Component;
    previewSheetId?: Ref<string | number | null>;
    openPreviewSheet?: Ref<boolean>;
    restoreRoute?: (row: TData) => string;
    forceDeleteRoute?: (row: TData) => string;
    onSuccess?: (response?: any) => void;
    onError?: (error: any) => void;
    // Nuevas propiedades para mejor control
    confirmMessages?: {
        delete?: string;
        restore?: string;
        permanentlyDelete?: string;
    };
    uiBehaviors?: {
        delete?: 'notifyRefresh' | 'refresh' | 'none';
        restore?: 'notifyRefresh' | 'refresh' | 'none';
        permanentlyDelete?: 'notifyRefresh' | 'refresh' | 'none';
    };
}

export interface BulkAction<TData extends Identifiable> {
    id: string;
    label: string;
    icon?: string | Component;
    endpoint: string;
    method?: 'POST' | 'PUT' | 'DELETE';
    permission?: string | string[];
    confirm?: boolean;
    confirmMessage?: string;
    uiBehavior?: 'notifyRefresh' | 'refresh' | 'none';
    onSuccess?: (response?: any) => void;
    onError?: (error: any) => void;
    disabled?: (selectedRows: Row<TData>[]) => boolean;
    variant: 'default' | 'destructive' | 'secondary' | 'outline';
    // Nuevas propiedades para mejor control
    loading?: boolean;
    tooltip?: string;
    badge?: string;
    badgeVariant?: 'default' | 'secondary' | 'destructive' | 'outline';
    showInDeletedMode?: boolean;
    showInNormalMode?: boolean;
    customParams?: Record<string, any>;
    transformData?: (selectedRows: Row<TData>[]) => any;
}

// Nuevas interfaces para mejor control de eliminación
export interface DeletionConfig {
    enabled: boolean;
    softDelete?: boolean;
    restoreEnabled?: boolean;
    permanentDeleteEnabled?: boolean;
    confirmMessages?: {
        delete?: string;
        restore?: string;
        permanentlyDelete?: string;
    };
    permissions?: {
        delete?: string | string[];
        restore?: string | string[];
        permanentlyDelete?: string | string[];
    };
    endpoints?: {
        delete?: string | ((id: string | number) => string);
        restore?: string | ((id: string | number) => string);
        permanentlyDelete?: string | ((id: string | number) => string);
    };
}

// Nueva interfaz para configuración de toolbar
export interface ToolbarConfig {
    showSearch?: boolean;
    showFilters?: boolean;
    showExport?: boolean;
    showDensityToggle?: boolean;
    showDeletedModeToggle?: boolean;
    showBulkActions?: boolean;
    searchPlaceholder?: string;
    filterGroups?: {
        basic?: string;
        advanced?: string;
    };
}

// Nueva interfaz para configuración de paginación
export interface PaginationConfig {
    showPagination?: boolean;
    pageSizeOptions?: number[];
    showPageSizeSelector?: boolean;
    showPageInfo?: boolean;
    showFirstLastButtons?: boolean;
    showPreviousNextButtons?: boolean;
}

export type ColumnDef<TData extends Identifiable, TValue = unknown> = TanStackColumnDef<TData, TValue>;
