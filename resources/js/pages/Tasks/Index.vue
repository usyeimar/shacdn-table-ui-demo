<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">Gestión de Tareas</h1>
          <p class="text-muted-foreground">
            Administra y organiza todas tus tareas de manera eficiente
          </p>
        </div>
        <Button @click="showCreateTaskModal = true">
          <Plus class="mr-2 h-4 w-4" />
          Nueva Tarea
        </Button>
      </div>

      <DataTable :endpoint="'/api/tasks'" :columns="taskColumns" :moduleId="'tasks'" :enableGlobalSearch="true"
        :enableColumnVisibility="true" :enableDeletedModeToggle="true" :initialPageSize="10"
        :searchPlaceholder="'Buscar tareas...'" :customFilters="filtersConfig" :exportConfig="exportConfig"
        :deletedMode="deletedMode" :enableRowSelection="true" :bulkActions="bulkActionsConfig"
        :rowActions="customRowActions" :defaultRowActionsConfig="defaultRowActionsConfig" :transformFn="transformTasks"
        @update:deletedMode="deletedMode = $event">
        <template #empty-state>
          <div class="flex flex-col items-center justify-center py-8">
            <Settings2 class="text-muted-foreground mb-2 h-12 w-12" />
            <p class="text-muted-foreground">No se encontraron tareas</p>
            <Button variant="link" class="mt-2" @click="showCreateTaskModal = true">
              <Plus class="mr-2 h-4 w-4" />
              Crear nueva tarea
            </Button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Create Task Modal -->
    <Dialog v-model:open="showCreateTaskModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Nueva Tarea</DialogTitle>
        </DialogHeader>
        <TaskForm @saved="handleTaskCreated" @cancel="showCreateTaskModal = false" />
      </DialogContent>
    </Dialog>

    <!-- Task Detail Sidebar -->
    <Sheet v-model:open="showTaskDetail" side="right">
      <SheetContent class="w-[400px] sm:w-[540px]">
        <SheetHeader>
          <SheetTitle>Detalle de Tarea</SheetTitle>
        </SheetHeader>
        <div class="mt-6">
          <TaskDetail v-if="selectedTask" :task="selectedTask" @edit="handleEdit" @deleted="handleTaskDeleted"
            @updated="handleTaskUpdated" />
        </div>
      </SheetContent>
    </Sheet>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, h, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { DataTable, DataTableRowActions, DateDisplayCell, MainDataCell } from '@/components/data-table';
import { Checkbox } from '@/components/ui/checkbox';
import TaskForm from '@/pages/Tasks/components/TaskForm.vue';
import TaskDetail from '@/pages/Tasks/components/TaskDetail.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus, Trash2Icon, PlusCircleIcon, Settings2 } from 'lucide-vue-next';
import axios from 'axios';
import { toast } from 'vue-sonner';
import type { FilterConfig, ExportConfig, PaginationMeta, BulkAction, DefaultRowActionsConfig, RowAction } from '@/components/data-table';
import type { ColumnDef } from '@tanstack/vue-table';
import type { BreadcrumbItemType } from '@/types';

interface User {
  id: string;
  name: string;
  email: string;
}

interface Task {
  id: string;
  title: string;
  description?: string;
  status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high';
  assigned_to?: string;
  assigned_user?: User;
  due_date?: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  mainData?: {
    name: string;
    display_name?: string;
    subtext?: string;
  };
}

// State
const selectedTask = ref<Task | null>(null);
const showCreateTaskModal = ref(false);
const showTaskDetail = ref(false);
const deletedMode = ref(false);

// Breadcrumbs
const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Tareas',
    href: '/tasks',
  },
];

// Configuración de columnas para la tabla de tareas
const taskColumns: ColumnDef<Task>[] = [

  {
    accessorKey: 'mainData',
    header: '',
    cell: ({ row }: { row: any }) => {
      const data = row.original as Task;
      const name = data.mainData?.display_name || data.title || 'N/A';
      const subtext = data.description || (data.assigned_user?.name ? `Asignado a: ${data.assigned_user.name}` : 'Sin asignar');
      return h(MainDataCell, {
        name,
        subtext,
        badges: data.priority ? [{ text: data.priority, variant: data.priority === 'high' ? 'destructive' : data.priority === 'medium' ? 'default' : 'secondary' }] : []
      });
    },
    size: 300,
    enableSorting: false,
    enableHiding: false
  },
  {
    accessorKey: 'title',
    header: 'Título',
    cell: ({ row }: { row: any }) => {
      const task = row.original as Task;
      return h('div', { class: 'font-medium cursor-pointer hover:text-primary' }, task.title);
    }
  },
  {
    accessorKey: 'description',
    header: 'Descripción',
    cell: ({ row }: { row: any }) => {
      const task = row.original as Task;
      const description = task.description ? (task.description.length > 50 ? task.description.substring(0, 50) + '...' : task.description) : 'Sin descripción';
      return h('div', { class: 'text-muted-foreground' }, description);
    }
  },
  {
    accessorKey: 'status',
    header: 'Estado',
    cell: ({ row }: { row: any }) => {
      const task = row.original as Task;
      const statusConfig: Record<string, { label: string; class: string }> = {
        pending: { label: 'Pendiente', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' },
        in_progress: { label: 'En Progreso', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' },
        completed: { label: 'Completada', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
        cancelled: { label: 'Cancelada', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }
      };

      const config = statusConfig[task.status] || statusConfig.pending;

      return h('span', {
        class: `px-2 py-1 text-xs font-medium rounded-full ${config.class}`
      }, config.label);
    }
  },
  {
    accessorKey: 'priority',
    header: 'Prioridad',
    cell: ({ row }: { row: any }) => {
      const task = row.original as Task;
      const priorityConfig: Record<string, { label: string; class: string }> = {
        low: { label: 'Baja', class: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' },
        medium: { label: 'Media', class: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300' },
        high: { label: 'Alta', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }
      };

      const config = priorityConfig[task.priority] || priorityConfig.medium;

      return h('span', {
        class: `px-2 py-1 text-xs font-medium rounded-full ${config.class}`
      }, config.label);
    }
  },
  {
    accessorKey: 'assigned_to',
    header: 'Asignado a',
    cell: ({ row }: { row: any }) => {
      const task = row.original as Task;
      const assignedTo = task.assigned_user?.name || 'Sin asignar';
      return h('div', { class: 'text-foreground' }, assignedTo);
    }
  },
  {
    accessorKey: 'due_date',
    header: 'Fecha de Vencimiento',
    cell: ({ getValue, row }: any) => {
      const val = getValue() as string | undefined;
      if (!val) return h('div', { class: 'text-muted-foreground' }, 'Sin fecha');
      return h(DateDisplayCell, { value: val, formatStr: 'dd MMM, yyyy' });
    },
    size: 150
  },
  {
    accessorKey: 'created_at',
    header: 'Creado',
    cell: ({ getValue }: any) => {
      const val = getValue() as string;
      return h(DateDisplayCell, { value: val, formatStr: 'dd MMM, yyyy' });
    },
    size: 150
  },
  {
    id: 'actions',
    header: () => h('div', { class: 'text-right' }, 'Acciones'),
    cell: ({ row, table }: any) =>
      h(DataTableRowActions as any, {
        row,
        table,
        customActions: customRowActions,
        defaultActionsConfig: defaultRowActionsConfig,
        isDeletedMode: deletedMode.value
      }),
    size: 80,
    enableResizing: false
  }
];

// Configuración de filtros
const filtersConfig: FilterConfig[] = [
  {
    key: 'status',
    label: 'Estado',
    type: 'select',
    options: [
      { value: 'pending', label: 'Pendiente' },
      { value: 'in_progress', label: 'En Progreso' },
      { value: 'completed', label: 'Completada' },
      { value: 'cancelled', label: 'Cancelada' }
    ],
    group: 'basic'
  },
  {
    key: 'priority',
    label: 'Prioridad',
    type: 'select',
    options: [
      { value: 'low', label: 'Baja' },
      { value: 'medium', label: 'Media' },
      { value: 'high', label: 'Alta' }
    ],
    group: 'basic'
  },
  {
    key: 'assigned_to',
    label: 'Asignado a',
    type: 'text',
    placeholder: 'Buscar por usuario asignado',
    group: 'basic'
  },
  {
    key: 'due_date',
    label: 'Fecha de Vencimiento',
    type: 'date-range',
    group: 'advanced'
  },
  {
    key: 'created_at',
    label: 'Fecha de Creación',
    type: 'date-range',
    group: 'advanced'
  },
  {
    key: 'overdue',
    label: 'Tareas Vencidas',
    type: 'select',
    options: [
      { value: 'true', label: 'Sí' },
      { value: 'false', label: 'No' }
    ],
    group: 'advanced'
  }
];

// Configuración de exportación (descarga como Blob)
const exportConfig = computed<ExportConfig>(() => ({
  formats: ['csv', 'xlsx'],
  endpoint: '/api/tasks/export',
  filename: `tasks-export-${new Date().toISOString().split('T')[0]}`,
  onExport: async (format) => {
    try {
      const response = await axios.get('/api/tasks/export', {
        params: { format, ...(deletedMode.value ? { trashed: true } : {}) },
        responseType: 'blob'
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `${exportConfig.value.filename}.${format}`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);

      toast.success('Exportación completada');
    } catch (error) {
      console.error('Error exporting tasks:', error);
      toast.error('Error al exportar las tareas');
      throw error as any;
    }
  }
}));

// Acciones masivas
const bulkActionsConfig = computed<BulkAction<Task>[]>(() => {
  if (deletedMode.value) {
    return [
      {
        id: 'bulk-restore',
        label: 'Restaurar Seleccionadas',
        icon: PlusCircleIcon,
        endpoint: '/api/tasks/bulk-restore',
        method: 'POST',
        permission: 'tasks.restore',
        confirm: true,
        confirmMessage: '¿Seguro que quieres restaurar las tareas seleccionadas?',
        uiBehavior: 'notifyRefresh',
        disabled: (selectedRows) => selectedRows.length === 0,
        variant: 'secondary'
      }
    ];
  }
  return [
    {
      id: 'bulk-delete',
      label: 'Eliminar Seleccionadas',
      icon: Trash2Icon,
      endpoint: '/api/tasks/bulk-delete',
      method: 'DELETE',
      permission: 'tasks.delete',
      confirm: true,
      confirmMessage: '¿Seguro que quieres eliminar las tareas seleccionadas?',
      uiBehavior: 'notifyRefresh',
      disabled: (selectedRows) => selectedRows.length === 0,
      variant: 'destructive'
    }
  ];
});

// Acciones por fila
const customRowActions = ref<RowAction<Task>[]>([]);
const defaultRowActionsConfig: DefaultRowActionsConfig<Task> = {
  normalMode: {
    delete: {
      permission: 'tasks.delete',
      enabled: true,
    },
  },
  deleteRoute: (row) => `/api/tasks/${row.id}`,
};

// Transformación de respuesta API
const transformTasks = (jsonData: { data: Task[]; meta: PaginationMeta }): { data: Task[]; meta: PaginationMeta } => {
  const data = (jsonData.data || []).map((item) => ({
    ...item,
    mainData: {
      name: item.title,
      display_name: item.title,
      subtext: item.description || (item.assigned_user?.name ? `Asignado a: ${item.assigned_user.name}` : 'Sin asignar'),
    },
  }));
  return { data, meta: jsonData.meta };
};

// Methods
const handleEdit = (task: any) => {
  // Aquí podrías abrir el modal de edición
  console.log('Edit task:', task);
};

const handleTaskCreated = (task: any) => {
  showCreateTaskModal.value = false;
  // Aquí podrías refrescar la tabla
};

const handleTaskDeleted = () => {
  showTaskDetail.value = false;
  selectedTask.value = null;
  // Aquí podrías refrescar la tabla
};

const handleTaskUpdated = (task: any) => {
  selectedTask.value = task;
  // Aquí podrías refrescar la tabla
};



</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
