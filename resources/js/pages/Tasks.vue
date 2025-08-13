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

      <DataTable
        :endpoint="'/api/tasks'"
        :columns="taskColumns"
        :moduleId="'tasks'"
        :enableGlobalSearch="false"
        :enableColumnVisibility="false"
        :enableRowSelection="true"
        :enableDeletedModeToggle="true"
        :initialPageSize="25"
        :searchPlaceholder="'Buscar tareas...'"
        :customFilters="filtersConfig"
        :exportConfig="exportConfig"
        :deletedMode="deletedMode"
        :show-bulk-actions="[
            { label: 'Eliminar', action: 'delete', permission: 'tasks.delete' },
            { label: 'Exportar', action: 'export', permission: 'tasks.export' }
        ]"
        :show-column-visibility-toggle="true"

        @update:deletedMode="deletedMode = $event"
      />
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
          <TaskDetail
            v-if="selectedTask"
            :task="selectedTask"
            @edit="handleEdit"
            @deleted="handleTaskDeleted"
            @updated="handleTaskUpdated"
          />
        </div>
      </SheetContent>
    </Sheet>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, h } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { DataTable } from '@/components/data-table';
import TaskForm from '@/components/TaskForm.vue';
import TaskDetail from '@/components/TaskDetail.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus } from 'lucide-vue-next';
import type { FilterConfig, ExportConfig } from '@/components/data-table';
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
    cell: ({ row }: { row: any }) => {
      const task = row.original as Task;
      if (!task.due_date) {
        return h('div', { class: 'text-muted-foreground' }, 'Sin fecha');
      }

      const dueDate = new Date(task.due_date);
      const today = new Date();
      const isOverdue = dueDate < today && task.status !== 'completed';

      return h('div', {
        class: isOverdue ? 'text-destructive font-medium' : 'text-foreground'
      }, new Date(task.due_date).toLocaleDateString('es-ES'));
    }
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

// Configuración de exportación
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
    console.log(`Exporting tasks in ${format} format`);
  }
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
