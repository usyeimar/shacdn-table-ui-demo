<script setup lang="ts">
// Vue and composables
import { ref, computed } from 'vue';

// Types
import type { ColumnDef } from '@tanstack/vue-table';
import type { ExportConfig, FilterConfig, RowAction, BulkAction } from '@/components/data-table';

// UI Components and Layouts
import { DataTable, DateDisplayCell } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

// Icons
import { Edit, Trash2, Eye, Undo2, Download, Plus, Calendar, User, AlertCircle } from 'lucide-vue-next';

interface Task {
    id: number;
    title: string;
    description: string;
    status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
    priority: 'low' | 'medium' | 'high';
    assigned_to: string;
    due_date: string;
    created_at: string;
    deleted_at?: string;
}

// Estado del modo eliminación
const deletedMode = ref(false);

// Configuración de exportación
const exportConfig: ExportConfig = {
    formats: ['csv', 'xlsx', 'pdf'],
    endpoint: '/api/tasks/export',
    filename: 'tasks-report',
    enabled: true,
    permission: 'tasks.export',
    showSelectedOnly: true,
    onExport: async (format) => {
        try {
            const response = await fetch(`/api/tasks/export?format=${format}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    format,
                    selectedRows: selectedRows.value,
                    filters: currentFilters.value
                })
            });
            
            if (!response.ok) throw new Error('Error en exportación');
            
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `tasks-${new Date().toISOString().split('T')[0]}.${format}`;
            a.click();
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Error exporting:', error);
        }
    }
};

// Filtros personalizados
const filtersConfig: FilterConfig[] = [
    {
        key: 'status',
        type: 'multiselect',
        label: 'Estado',
        placeholder: 'Selecciona estados...',
        options: [
            { value: 'pending', label: 'Pendiente' },
            { value: 'in_progress', label: 'En Progreso' },
            { value: 'completed', label: 'Completado' },
            { value: 'cancelled', label: 'Cancelado' }
        ],
        group: 'basic',
        enabled: true
    },
    {
        key: 'priority',
        type: 'select',
        label: 'Prioridad',
        placeholder: 'Selecciona prioridad...',
        options: [
            { value: 'low', label: 'Baja' },
            { value: 'medium', label: 'Media' },
            { value: 'high', label: 'Alta' }
        ],
        group: 'basic',
        enabled: true
    },
    {
        key: 'due_date',
        type: 'date-range',
        label: 'Fecha de vencimiento',
        placeholder: 'Selecciona rango...',
        group: 'advanced',
        enabled: true
    },
    {
        key: 'assigned_to',
        type: 'text',
        label: 'Asignado a',
        placeholder: 'Buscar por asignado...',
        group: 'basic',
        enabled: true
    }
];

// Acciones por fila
const rowActions: RowAction<Task>[] = [
    {
        label: 'Ver',
        icon: Eye,
        actionKind: 'route',
        routeName: 'tasks.show',
        routeParams: (row) => ({ task: row.id }),
        permission: 'tasks.read',
        order: 1,
        showInNormalMode: true,
        showInDeletedMode: false,
        tooltip: 'Ver detalles de la tarea'
    },
    {
        label: 'Editar',
        icon: Edit,
        actionKind: 'route',
        routeName: 'tasks.edit',
        routeParams: (row) => ({ task: row.id }),
        permission: 'tasks.update',
        order: 2,
        showInNormalMode: true,
        showInDeletedMode: false,
        disabled: (row) => row.status === 'completed',
        tooltip: 'Editar tarea'
    },
    {
        label: 'Eliminar',
        icon: Trash2,
        actionKind: 'http',
        httpMethod: 'DELETE',
        endpoint: (row) => `/api/tasks/${row.id}`,
        permission: 'tasks.delete',
        confirm: true,
        confirmMessage: '¿Estás seguro de que quieres eliminar esta tarea?',
        uiBehavior: 'notifyRefresh',
        order: 3,
        showInNormalMode: true,
        showInDeletedMode: false,
        class: 'text-red-600',
        tooltip: 'Eliminar tarea'
    }
];

// Acciones para elementos eliminados
const deletedRowActions: RowAction<Task>[] = [
    {
        label: 'Restaurar',
        icon: Undo2,
        actionKind: 'http',
        httpMethod: 'POST',
        endpoint: (row) => `/api/tasks/${row.id}/restore`,
        permission: 'tasks.restore',
        uiBehavior: 'notifyRefresh',
        confirm: true,
        confirmMessage: '¿Estás seguro de que quieres restaurar esta tarea?',
        showInNormalMode: false,
        showInDeletedMode: true,
        class: 'text-green-600',
        tooltip: 'Restaurar tarea'
    },
    {
        label: 'Eliminar Permanentemente',
        icon: Trash2,
        actionKind: 'http',
        httpMethod: 'DELETE',
        endpoint: (row) => `/api/tasks/${row.id}/force-delete`,
        permission: 'tasks.forceDelete',
        uiBehavior: 'notifyRefresh',
        confirm: true,
        confirmMessage: '¿Estás seguro? Esta acción no se puede deshacer.',
        showInNormalMode: false,
        showInDeletedMode: true,
        class: 'text-red-600',
        tooltip: 'Eliminar permanentemente'
    }
];

// Acciones masivas
const bulkActions: BulkAction<Task>[] = [
    {
        id: 'delete',
        label: 'Eliminar Seleccionadas',
        icon: Trash2,
        endpoint: '/api/tasks/bulk-delete',
        method: 'DELETE',
        permission: 'tasks.delete',
        confirm: true,
        confirmMessage: '¿Estás seguro de que quieres eliminar las tareas seleccionadas?',
        uiBehavior: 'notifyRefresh',
        variant: 'destructive',
        showInNormalMode: true,
        showInDeletedMode: false,
        disabled: (selectedRows) => selectedRows.length === 0,
        tooltip: 'Eliminar tareas seleccionadas'
    },
    {
        id: 'export',
        label: 'Exportar Seleccionadas',
        icon: Download,
        endpoint: '/api/tasks/bulk-export',
        method: 'POST',
        permission: 'tasks.export',
        uiBehavior: 'none',
        variant: 'outline',
        showInNormalMode: true,
        showInDeletedMode: false,
        disabled: (selectedRows) => selectedRows.length === 0,
        tooltip: 'Exportar tareas seleccionadas'
    },
    {
        id: 'restore',
        label: 'Restaurar Seleccionadas',
        icon: Undo2,
        endpoint: '/api/tasks/bulk-restore',
        method: 'POST',
        permission: 'tasks.restore',
        confirm: true,
        confirmMessage: '¿Estás seguro de que quieres restaurar las tareas seleccionadas?',
        uiBehavior: 'notifyRefresh',
        variant: 'default',
        showInNormalMode: false,
        showInDeletedMode: true,
        disabled: (selectedRows) => selectedRows.length === 0,
        tooltip: 'Restaurar tareas seleccionadas'
    }
];

// Variables reactivas para el ejemplo
const selectedRows = ref([]);
const currentFilters = ref({});

const taskColumns: ColumnDef<Task>[] = [
    {
        accessorKey: 'title',
        header: 'Título',
        cell: ({ row }) => (
            <div class="max-w-xs">
                <div class="font-medium truncate">{row.original.title}</div>
                <div class="text-xs text-muted-foreground truncate">{row.original.description}</div>
            </div>
        ),
        size: 250
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        cell: ({ row }) => {
            const status = row.original.status;
            const variants = {
                pending: 'secondary',
                in_progress: 'default',
                completed: 'success',
                cancelled: 'destructive'
            };
            const labels = {
                pending: 'Pendiente',
                in_progress: 'En Progreso',
                completed: 'Completado',
                cancelled: 'Cancelado'
            };
            return h(Badge, { variant: variants[status] }, () => labels[status]);
        },
        size: 120
    },
    {
        accessorKey: 'priority',
        header: 'Prioridad',
        cell: ({ row }) => {
            const priority = row.original.priority;
            const variants = {
                low: 'secondary',
                medium: 'default',
                high: 'destructive'
            };
            const labels = {
                low: 'Baja',
                medium: 'Media',
                high: 'Alta'
            };
            return h(Badge, { variant: variants[priority] }, () => labels[priority]);
        },
        size: 100
    },
    {
        accessorKey: 'assigned_to',
        header: 'Asignado a',
        cell: ({ row }) => (
            <div class="flex items-center gap-2">
                <User class="h-4 w-4 text-muted-foreground" />
                <span>{row.original.assigned_to}</span>
            </div>
        ),
        size: 150
    },
    {
        accessorKey: 'due_date',
        header: 'Fecha de vencimiento',
        cell: ({ row }) => (
            <div class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-muted-foreground" />
                {h(DateDisplayCell, { value: row.original.due_date })}
            </div>
        ),
        size: 150
    },
    {
        accessorKey: 'created_at',
        header: 'Creado',
        cell: ({ row }) => h(DateDisplayCell, { value: row.original.created_at }),
        size: 120
    }
];

// Controles de configuración
const compactMode = ref(false);
const density = ref<'compact' | 'normal' | 'comfortable'>('normal');
const rowHeight = ref<'sm' | 'md' | 'lg'>('md');
const showToolbar = ref(true);
const showPagination = ref(true);
const showBulkActions = ref(true);
const showExport = ref(true);
const showDeletedModeToggle = ref(true);
</script>

<template>
    <div class="space-y-6">
        <!-- Controles de configuración -->
        <div class="rounded-lg border p-4 space-y-4">
            <h3 class="text-lg font-semibold">Configuración de Densidad y Funcionalidades</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm font-medium">Densidad</label>
                    <select v-model="density" class="w-full mt-1 rounded-md border px-3 py-2 text-sm">
                        <option value="compact">Compacta</option>
                        <option value="normal">Normal</option>
                        <option value="comfortable">Comfortable</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-sm font-medium">Altura de Filas</label>
                    <select v-model="rowHeight" class="w-full mt-1 rounded-md border px-3 py-2 text-sm">
                        <option value="sm">Pequeña</option>
                        <option value="md">Mediana</option>
                        <option value="lg">Grande</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-sm font-medium">Modo Compacto</label>
                    <select v-model="compactMode" class="w-full mt-1 rounded-md border px-3 py-2 text-sm">
                        <option :value="false">Desactivado</option>
                        <option :value="true">Activado</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-sm font-medium">Modo Eliminación</label>
                    <select v-model="deletedMode" class="w-full mt-1 rounded-md border px-3 py-2 text-sm">
                        <option :value="false">Normal</option>
                        <option :value="true">Eliminados</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="showToolbar" class="rounded">
                    Toolbar
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="showPagination" class="rounded">
                    Paginación
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="showBulkActions" class="rounded">
                    Acciones Masivas
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="showExport" class="rounded">
                    Exportación
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="showDeletedModeToggle" class="rounded">
                    Toggle Eliminación
                </label>
            </div>
        </div>

        <!-- Tabla con configuración dinámica -->
        <DataTable
            :endpoint="'/api/tasks'"
            :columns="taskColumns"
            :moduleId="'tasks-compact-example'"
            :density="density"
            :rowHeight="rowHeight"
            :compactMode="compactMode"
            :showToolbar="showToolbar"
            :showPagination="showPagination"
            :showBulkActions="showBulkActions"
            :enableRowSelection="true"
            :enableGlobalSearch="showToolbar"
            :enableColumnVisibility="showToolbar"
            :initialPageSize="10"
            :exportConfig="showExport ? exportConfig : undefined"
            :customFilters="filtersConfig"
            :rowActions="rowActions"
            :deletedRowActions="deletedRowActions"
            :bulkActions="bulkActions"
            :deletedMode="deletedMode"
            :enableDeletedModeToggle="showDeletedModeToggle"
            searchPlaceholder="Buscar tareas..."
            @update:deletedMode="deletedMode = $event"
        />
    </div>
</template> 