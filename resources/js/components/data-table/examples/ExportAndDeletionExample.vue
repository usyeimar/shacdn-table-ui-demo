<script setup lang="ts">
// Vue and composables
import { ref, computed } from 'vue';

// Types
import type { ColumnDef } from '@tanstack/vue-table';
import type { 
    ExportConfig, 
    FilterConfig, 
    RowAction, 
    BulkAction, 
    DefaultRowActionsConfig 
} from '@/components/data-table';

// UI Components and Layouts
import { DataTable, DateDisplayCell } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

// Icons
import { 
    Edit, 
    Trash2, 
    Eye, 
    Undo2, 
    Download, 
    Plus, 
    Calendar, 
    User, 
    AlertCircle,
    FileText,
    Archive,
    RotateCcw
} from 'lucide-vue-next';

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

// Configuración de exportación avanzada
const exportConfig: ExportConfig = {
    formats: ['csv', 'xlsx', 'pdf', 'json'],
    endpoint: '/api/tasks/export',
    filename: 'tasks-report',
    enabled: true,
    permission: 'tasks.export',
    showSelectedOnly: true,
    customHeaders: {
        'X-Export-Type': 'tasks',
        'X-User-Id': 'current-user-id'
    },
    customParams: {
        includeMetadata: true,
        includeFilters: true
    },
    onExport: async (format) => {
        try {
            // Mostrar indicador de carga
            const loadingToast = toast.loading('Preparando exportación...');
            
            const response = await fetch(`/api/tasks/export?format=${format}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Export-Type': 'tasks',
                    'X-User-Id': 'current-user-id'
                },
                body: JSON.stringify({
                    format,
                    selectedRows: selectedRows.value,
                    filters: currentFilters.value,
                    includeMetadata: true,
                    includeFilters: true,
                    deletedMode: deletedMode.value
                })
            });
            
            if (!response.ok) {
                throw new Error(`Error en exportación: ${response.statusText}`);
            }
            
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `tasks-${new Date().toISOString().split('T')[0]}.${format}`;
            a.click();
            window.URL.revokeObjectURL(url);
            
            // Cerrar indicador de carga y mostrar éxito
            toast.dismiss(loadingToast);
            toast.success(`Exportación completada en formato ${format.toUpperCase()}`);
        } catch (error) {
            console.error('Error exporting:', error);
            toast.error('Error al exportar los datos');
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
        enabled: true,
        defaultValue: ['pending', 'in_progress']
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
        enabled: true,
        validation: {
            required: false
        }
    },
    {
        key: 'assigned_to',
        type: 'text',
        label: 'Asignado a',
        placeholder: 'Buscar por asignado...',
        group: 'basic',
        enabled: true,
        transform: (value) => value?.toLowerCase()
    }
];

// Configuración de acciones por defecto
const defaultRowActionsConfig = computed<DefaultRowActionsConfig<Task>>(() => ({
    normalMode: {
        view: {
            permission: 'tasks.read',
            enabled: true,
            order: 1,
            confirm: false,
            uiBehavior: 'none'
        },
        edit: {
            permission: 'tasks.update',
            enabled: true,
            order: 2,
            confirm: false,
            uiBehavior: 'none'
        },
        delete: {
            permission: 'tasks.delete',
            enabled: true,
            order: 3,
            confirm: true,
            confirmMessage: '¿Estás seguro de que quieres eliminar esta tarea?',
            uiBehavior: 'notifyRefresh'
        }
    },
    deletedMode: {
        restore: {
            permission: 'tasks.restore',
            enabled: true,
            order: 1,
            confirm: true,
            confirmMessage: '¿Estás seguro de que quieres restaurar esta tarea?',
            uiBehavior: 'notifyRefresh'
        },
        permanentlyDelete: {
            permission: 'tasks.forceDelete',
            enabled: true,
            order: 2,
            confirm: true,
            confirmMessage: '¿Estás seguro? Esta acción no se puede deshacer.',
            uiBehavior: 'notifyRefresh'
        }
    },
    viewRoute: () => 'tasks.show',
    editRoute: () => 'tasks.edit',
    deleteRoute: (row) => `/api/tasks/${row.id}`,
    restoreRoute: (row) => `/api/tasks/${row.id}/restore`,
    forceDeleteRoute: (row) => `/api/tasks/${row.id}/force-delete`,
    confirmMessages: {
        delete: '¿Estás seguro de que quieres eliminar esta tarea?',
        restore: '¿Estás seguro de que quieres restaurar esta tarea?',
        permanentlyDelete: '¿Estás seguro? Esta acción no se puede deshacer.'
    },
    uiBehaviors: {
        delete: 'notifyRefresh',
        restore: 'notifyRefresh',
        permanentlyDelete: 'notifyRefresh'
    }
}));

// Acciones personalizadas por fila
const rowActions: RowAction<Task>[] = [
    {
        label: 'Ver Detalles',
        icon: Eye,
        actionKind: 'route',
        routeName: 'tasks.show',
        routeParams: (row) => ({ task: row.id }),
        permission: 'tasks.read',
        order: 1,
        showInNormalMode: true,
        showInDeletedMode: false,
        tooltip: 'Ver detalles completos de la tarea'
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
        tooltip: 'Editar tarea (no disponible para tareas completadas)'
    },
    {
        label: 'Archivar',
        icon: Archive,
        actionKind: 'http',
        httpMethod: 'POST',
        endpoint: (row) => `/api/tasks/${row.id}/archive`,
        permission: 'tasks.archive',
        confirm: true,
        confirmMessage: '¿Estás seguro de que quieres archivar esta tarea?',
        uiBehavior: 'notifyRefresh',
        order: 3,
        showInNormalMode: true,
        showInDeletedMode: false,
        class: 'text-orange-600',
        tooltip: 'Archivar tarea'
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
        order: 4,
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
        icon: RotateCcw,
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
        tooltip: 'Restaurar tarea eliminada'
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
        tooltip: 'Eliminar permanentemente (irreversible)'
    }
];

// Acciones masivas
const bulkActions: BulkAction<Task>[] = [
    {
        id: 'archive',
        label: 'Archivar Seleccionadas',
        icon: Archive,
        endpoint: '/api/tasks/bulk-archive',
        method: 'POST',
        permission: 'tasks.archive',
        confirm: true,
        confirmMessage: '¿Estás seguro de que quieres archivar las tareas seleccionadas?',
        uiBehavior: 'notifyRefresh',
        variant: 'outline',
        showInNormalMode: true,
        showInDeletedMode: false,
        disabled: (selectedRows) => selectedRows.length === 0,
        tooltip: 'Archivar tareas seleccionadas'
    },
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
        tooltip: 'Exportar tareas seleccionadas',
        customParams: {
            selectedOnly: true
        }
    },
    {
        id: 'restore',
        label: 'Restaurar Seleccionadas',
        icon: RotateCcw,
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
    },
    {
        id: 'force-delete',
        label: 'Eliminar Permanentemente',
        icon: Trash2,
        endpoint: '/api/tasks/bulk-force-delete',
        method: 'DELETE',
        permission: 'tasks.forceDelete',
        confirm: true,
        confirmMessage: '¿Estás seguro? Esta acción no se puede deshacer.',
        uiBehavior: 'notifyRefresh',
        variant: 'destructive',
        showInNormalMode: false,
        showInDeletedMode: true,
        disabled: (selectedRows) => selectedRows.length === 0,
        tooltip: 'Eliminar permanentemente las tareas seleccionadas'
    }
];

// Variables reactivas
const selectedRows = ref([]);
const currentFilters = ref({});

// Columnas de la tabla
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

// Estadísticas
const stats = computed(() => ({
    total: 150,
    pending: 45,
    inProgress: 30,
    completed: 60,
    cancelled: 15,
    deleted: 25
}));
</script>

<template>
    <div class="space-y-6">
        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-2">
                        <FileText class="h-4 w-4 text-blue-600" />
                        <div>
                            <p class="text-sm text-muted-foreground">Total</p>
                            <p class="text-2xl font-bold">{{ stats.total }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-2">
                        <AlertCircle class="h-4 w-4 text-orange-600" />
                        <div>
                            <p class="text-sm text-muted-foreground">Pendientes</p>
                            <p class="text-2xl font-bold">{{ stats.pending }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-blue-600" />
                        <div>
                            <p class="text-sm text-muted-foreground">En Progreso</p>
                            <p class="text-2xl font-bold">{{ stats.inProgress }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4 text-green-600" />
                        <div>
                            <p class="text-sm text-muted-foreground">Completadas</p>
                            <p class="text-2xl font-bold">{{ stats.completed }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-2">
                        <XCircle class="h-4 w-4 text-red-600" />
                        <div>
                            <p class="text-sm text-muted-foreground">Canceladas</p>
                            <p class="text-2xl font-bold">{{ stats.cancelled }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-2">
                        <Archive class="h-4 w-4 text-gray-600" />
                        <div>
                            <p class="text-sm text-muted-foreground">Eliminadas</p>
                            <p class="text-2xl font-bold">{{ stats.deleted }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Tabla principal -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="text-xl">
                            {{ deletedMode ? 'Tareas Eliminadas' : 'Gestión de Tareas' }}
                        </CardTitle>
                        <CardDescription>
                            {{ deletedMode ? 'Gestiona las tareas eliminadas' : 'Administra y organiza tus tareas' }}
                        </CardDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="!deletedMode"
                            size="sm"
                            @click="() => router.visit('/tasks/create')"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Nueva Tarea
                        </Button>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <DataTable
                    :endpoint="'/api/tasks'"
                    :columns="taskColumns"
                    :moduleId="'tasks-export-deletion'"
                    :exportConfig="exportConfig"
                    :customFilters="filtersConfig"
                    :rowActions="rowActions"
                    :deletedRowActions="deletedRowActions"
                    :defaultRowActionsConfig="defaultRowActionsConfig"
                    :bulkActions="bulkActions"
                    :deletedMode="deletedMode"
                    :enableDeletedModeToggle="true"
                    :enableGlobalSearch="true"
                    :enableColumnVisibility="true"
                    :enableRowSelection="true"
                    :initialPageSize="10"
                    :density="'normal'"
                    :rowHeight="'md'"
                    searchPlaceholder="Buscar tareas..."
                    @update:deletedMode="deletedMode = $event"
                />
            </CardContent>
        </Card>
    </div>
</template> 