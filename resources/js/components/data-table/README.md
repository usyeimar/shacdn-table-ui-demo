# 🎯 DataTable Component

El componente DataTable es una tabla de datos avanzada y flexible que proporciona funcionalidades robustas para la visualización y manipulación de datos tabulares. Está construido sobre TanStack Table (Vue Table) y está optimizado para trabajar con APIs RESTful.

## 📋 Tabla de Contenidos

- [✨ Características Principales](#-características-principales)
- [🚀 Instalación](#-instalación)
- [🧩 Componentes](#-componentes)
- [💡 Uso Básico](#-uso-básico)
- [⚙️ Configuración Avanzada](#️-configuración-avanzada)
- [📤 Exportación de Datos](#-exportación-de-datos)
- [🗑️ Gestión de Eliminación](#️-gestión-de-eliminación)
- [📚 API Reference](#-api-reference)
- [🎨 Ejemplos Prácticos](#-ejemplos-prácticos)
- [✅ Mejores Prácticas](#-mejores-prácticas)
- [🔧 Solución de Problemas](#-solución-de-problemas)

## ✨ Características Principales

### 📊 Visualización de Datos
- 🔄 Paginación del lado del servidor
- 📏 Control de tamaño de página
- 🧭 Navegación intuitiva
- 🎛️ **Control de densidad (compact, normal, comfortable)**
- 📐 **Altura de filas configurable (sm, md, lg)**
- 🎯 **Modo compacto para máxima eficiencia de espacio**

### 🔍 Búsqueda y Filtrado
- ⚡ Búsqueda global en tiempo real
- 🎯 Filtros personalizados por columna
- 🔎 Filtros avanzados con múltiples condiciones
- 🏷️ Etiquetas de filtros activos

### 🎨 Interfaz de Usuario
- 💫 Estados de carga con skeletons
- ⚠️ Mensajes de error personalizables
- 🌓 Soporte para temas claros/oscuros
- 🎭 Personalización de estilos
- 🎛️ **Control de visibilidad de elementos (toolbar, paginación, bulk actions)**

### ⚡ Funcionalidades Avanzadas
- 🔄 Ordenamiento de columnas
- 👁️ Control de visibilidad de columnas
- ✅ Selección de filas
- 📥 **Exportación de datos (CSV, Excel, PDF, JSON)**
- 🛠️ Acciones por fila y acciones masivas
- 🔒 Control de permisos granular
- 🗑️ **Modo de eliminación con restauración**

## 🚀 Instalación

```bash
# 📦 Instalar dependencias principales
npm install @tanstack/vue-table @tanstack/vue-query axios

# 🎨 Instalar dependencias opcionales
npm install lucide-vue-next vue-sonner
```

## 🧩 Componentes

### 🎯 Core Components

| Componente | Descripción | Props Principales |
|------------|-------------|-------------------|
| `DataTable` | 📊 Componente principal | `endpoint`, `columns`, `moduleId` |
| `DataTablePagination` | 📄 Control de paginación | `table`, `totalItems` |
| `DataTableRowActions` | ⚡ Acciones por fila | `row`, `actions`, `permissions` |
| `DataTableToolbar` | 🛠️ Barra de herramientas | `filters`, `search`, `export` |
| `DateDisplayCell` | 📅 Celda para fechas | `date`, `format` |
| `MainDataCell` | 📝 Celda principal | `value`, `type` |

### 🎨 Feature Components

| Componente | Descripción | Uso |
|------------|-------------|-----|
| `ExportDialog` | 📥 Exportación de datos | Configuración de formatos y endpoints |

## 💡 Uso Básico

### 📝 Configuración Mínima

```vue
<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
  />
</template>

<script setup lang="ts">
import { DataTable } from '@/components/data-table';
import { ref } from 'vue';

const taskColumns = [
  {
    accessorKey: 'title',
    header: 'Título',
  },
  {
    accessorKey: 'status',
    header: 'Estado',
  },
  {
    accessorKey: 'priority',
    header: 'Prioridad',
  }
];
</script>
```

### ⚙️ Configuración Completa

```vue
<template>
  <DataTable
    :endpoint="endpoint"
    :columns="columns"
    :moduleId="moduleId"
    :initialPageSize="15"
    :enableGlobalSearch="true"
    :enableColumnVisibility="true"
    :enableRowSelection="true"
    :rowActions="rowActions"
    :bulkActions="bulkActions"
    :customFilters="customFilters"
    :exportConfig="exportConfig"
    :deletedMode="deletedMode"
    :enableDeletedModeToggle="true"
    @row-selected="handleRowSelected"
    @bulk-action="handleBulkAction"
  />
</template>
```

## 📤 Exportación de Datos

### 🔧 Configuración de Exportación

El componente DataTable soporta exportación en múltiples formatos:

```vue
<script setup lang="ts">
import { DataTable, type ExportConfig } from '@/components/data-table';
import { ref } from 'vue';

const exportConfig: ExportConfig = {
  formats: ['csv', 'xlsx', 'pdf', 'json'],
  endpoint: '/api/tasks/export',
  filename: 'tasks-export',
  onExport: async (format) => {
    // Lógica personalizada de exportación
    const response = await fetch(`/api/tasks/export?format=${format}`);
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `tasks-export.${format}`;
    a.click();
  }
};
</script>

<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    :exportConfig="exportConfig"
  />
</template>
```

### 📋 Formatos Soportados

| Formato | Descripción | Uso Recomendado |
|---------|-------------|-----------------|
| `csv` | Archivo separado por comas | 📊 Datos simples, análisis en Excel |
| `xlsx` | Hoja de cálculo Excel | 📈 Reportes complejos, gráficos |
| `pdf` | Documento PDF | 📄 Reportes formales, impresión |
| `json` | Formato JSON | 🔧 Integración con APIs, desarrollo |

### 🎯 Ejemplo Completo de Exportación

```vue
<script setup lang="ts">
import { DataTable, type ExportConfig, type FilterConfig } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Edit, Trash2, Eye } from 'lucide-vue-next';
import { computed } from 'vue';

// Tipos
interface Task {
  id: number;
  title: string;
  description: string;
  status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high';
  assigned_to: string;
  due_date: string;
  created_at: string;
}

// Configuración de exportación
const exportConfig: ExportConfig = {
  formats: ['csv', 'xlsx', 'pdf'],
  endpoint: '/api/tasks/export',
  filename: 'tasks-report',
  onExport: async (format) => {
    try {
      const response = await fetch(`/api/tasks/export?format=${format}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          filters: currentFilters.value,
          selectedRows: selectedRows.value
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
    group: 'basic'
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
    group: 'basic'
  },
  {
    key: 'due_date',
    type: 'date-range',
    label: 'Fecha de vencimiento',
    placeholder: 'Selecciona rango...',
    group: 'advanced'
  }
];

// Acciones por fila
const rowActions = [
  {
    label: 'Ver',
    icon: Eye,
    actionKind: 'route',
    routeName: 'tasks.show',
    routeParams: (row: Task) => ({ task: row.id }),
    permission: 'tasks.read',
    order: 1
  },
  {
    label: 'Editar',
    icon: Edit,
    actionKind: 'route',
    routeName: 'tasks.edit',
    routeParams: (row: Task) => ({ task: row.id }),
    permission: 'tasks.update',
    order: 2
  },
  {
    label: 'Eliminar',
    icon: Trash2,
    actionKind: 'http',
    httpMethod: 'DELETE',
    endpoint: (row: Task) => `/api/tasks/${row.id}`,
    permission: 'tasks.delete',
    confirm: true,
    confirmMessage: '¿Estás seguro de que quieres eliminar esta tarea?',
    uiBehavior: 'notifyRefresh',
    order: 3
  }
];

// Columnas de la tabla
const columns = [
  {
    accessorKey: 'title',
    header: 'Título',
    cell: ({ row }) => row.original.title,
    size: 200
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
    cell: ({ row }) => row.original.assigned_to,
    size: 150
  },
  {
    accessorKey: 'due_date',
    header: 'Fecha de vencimiento',
    cell: ({ row }) => h(DateDisplayCell, { value: row.original.due_date }),
    size: 150
  }
];
</script>

<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="columns"
    :moduleId="'tasks'"
    :exportConfig="exportConfig"
    :customFilters="filtersConfig"
    :rowActions="rowActions"
    :enableGlobalSearch="true"
    :enableColumnVisibility="true"
    :enableRowSelection="true"
    :initialPageSize="10"
    searchPlaceholder="Buscar tareas..."
  />
</template>
```

## 🗑️ Gestión de Eliminación

### 🔄 Modo de Eliminación

El componente soporta un modo especial para gestionar elementos eliminados:

```vue
<script setup lang="ts">
import { DataTable, type DefaultRowActionsConfig } from '@/components/data-table';
import { ref, computed } from 'vue';

const deletedMode = ref(false);

// Configuración de acciones por defecto
const defaultRowActionsConfig = computed<DefaultRowActionsConfig<Task>>(() => ({
  normalMode: {
    view: {
      permission: 'tasks.read',
      enabled: true,
      order: 1
    },
    edit: {
      permission: 'tasks.update',
      enabled: true,
      order: 2
    },
    delete: {
      permission: 'tasks.delete',
      enabled: true,
      order: 3
    }
  },
  deletedMode: {
    restore: {
      permission: 'tasks.restore',
      enabled: true,
      order: 1
    },
    permanentlyDelete: {
      permission: 'tasks.forceDelete',
      enabled: true,
      order: 2
    }
  },
  viewRoute: () => 'tasks.show',
  editRoute: () => 'tasks.edit',
  deleteRoute: (row) => `/api/tasks/${row.id}`,
  restoreRoute: (row) => `/api/tasks/${row.id}/restore`,
  forceDeleteRoute: (row) => `/api/tasks/${row.id}/force-delete`
}));

// Acciones personalizadas para elementos eliminados
const deletedRowActions = [
  {
    label: 'Restaurar',
    icon: Undo2,
    actionKind: 'http',
    httpMethod: 'POST',
    endpoint: (row: Task) => `/api/tasks/${row.id}/restore`,
    permission: 'tasks.restore',
    uiBehavior: 'notifyRefresh',
    confirm: true,
    confirmMessage: '¿Estás seguro de que quieres restaurar esta tarea?'
  },
  {
    label: 'Eliminar Permanentemente',
    icon: Trash2,
    actionKind: 'http',
    httpMethod: 'DELETE',
    endpoint: (row: Task) => `/api/tasks/${row.id}/force-delete`,
    permission: 'tasks.forceDelete',
    uiBehavior: 'notifyRefresh',
    confirm: true,
    confirmMessage: '¿Estás seguro? Esta acción no se puede deshacer.',
    class: 'text-red-600'
  }
];
</script>

<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="columns"
    :moduleId="'tasks'"
    :deletedMode="deletedMode"
    :enableDeletedModeToggle="true"
    :defaultRowActionsConfig="defaultRowActionsConfig"
    :deletedRowActions="deletedRowActions"
    @update:deletedMode="deletedMode = $event"
  />
</template>
```

### 🎛️ Configuración de Acciones por Defecto

```typescript
interface DefaultRowActionsConfig<TData> {
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
  editRoute?: (row: TData) => string;
  deleteRoute?: (row: TData) => string;
  restoreRoute?: (row: TData) => string;
  forceDeleteRoute?: (row: TData) => string;
}
```

## 🎯 Configuración de Densidad y Compactación

### 📐 Control de Densidad

El componente DataTable soporta tres niveles de densidad para optimizar el uso del espacio:

```vue
<template>
  <!-- Tabla compacta para máxima eficiencia de espacio -->
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    density="compact"
    rowHeight="sm"
    compactMode="true"
  />

  <!-- Tabla normal (por defecto) -->
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    density="normal"
    rowHeight="md"
  />

  <!-- Tabla comfortable para mejor legibilidad -->
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    density="comfortable"
    rowHeight="lg"
  />
</template>
```

### 🎛️ Opciones de Densidad

| Densidad | Tamaño de Texto | Padding | Uso Recomendado |
|----------|-----------------|---------|------------------|
| `compact` | `text-xs` | `px-2 py-1` | 📊 Tablas con muchos datos |
| `normal` | `text-sm` | `px-3 py-2.5` | 🎯 Uso general |
| `comfortable` | `text-base` | `px-4 py-3` | 📖 Tablas de lectura |

### 📏 Altura de Filas

```vue
<template>
  <!-- Filas pequeñas -->
  <DataTable rowHeight="sm" />

  <!-- Filas medianas (por defecto) -->
  <DataTable rowHeight="md" />

  <!-- Filas grandes -->
  <DataTable rowHeight="lg" />
</template>
```

### 🎯 Modo Compacto

El modo compacto reduce el espaciado entre elementos:

```vue
<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    compactMode="true"
    :showToolbar="false"
    :showPagination="false"
  />
</template>
```

### 👁️ Control de Visibilidad

Puedes ocultar elementos opcionales para crear tablas más limpias:

```vue
<template>
  <!-- Tabla minimalista -->
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    :showToolbar="false"
    :showPagination="false"
    :showBulkActions="false"
    density="compact"
    rowHeight="sm"
  />
</template>
```

## 📚 API Reference

### 📋 Props

| Prop | Tipo | Requerido | Default | Descripción |
|------|------|-----------|---------|-------------|
| `endpoint` | string | ✅ | - | 🌐 URL del endpoint para obtener datos |
| `columns` | ColumnDef[] | ✅ | - | 📊 Definición de columnas |
| `moduleId` | string | ✅ | - | 🏷️ Identificador único del módulo |
| `initialPageSize` | number | ❌ | 15 | 📏 Tamaño inicial de página |
| `enableGlobalSearch` | boolean | ❌ | false | 🔍 Habilita búsqueda global |
| `enableColumnVisibility` | boolean | ❌ | false | 👁️ Habilita control de visibilidad |
| `enableRowSelection` | boolean | ❌ | true | ✅ Habilita selección de filas |
| `rowActions` | RowAction[] | ❌ | [] | ⚡ Acciones por fila |
| `deletedRowActions` | RowAction[] | ❌ | [] | 🗑️ Acciones para elementos eliminados |
| `defaultRowActionsConfig` | DefaultRowActionsConfig | ❌ | {} | ⚙️ Configuración de acciones por defecto |
| `bulkActions` | BulkAction[] | ❌ | [] | 🎯 Acciones masivas |
| `customFilters` | FilterConfig[] | ❌ | [] | 🔍 Filtros personalizados |
| `exportConfig` | ExportConfig | ❌ | undefined | 📥 Configuración de exportación |
| `deletedMode` | boolean | ❌ | false | 🗑️ Modo de elementos eliminados |
| `enableDeletedModeToggle` | boolean | ❌ | false | 🔄 Habilita toggle de modo eliminación |
| `density` | 'compact' \| 'normal' \| 'comfortable' | ❌ | 'normal' | 🎛️ Control de densidad de la tabla |
| `rowHeight` | 'sm' \| 'md' \| 'lg' | ❌ | 'md' | 📐 Altura de las filas |
| `compactMode` | boolean | ❌ | false | 🎯 Modo compacto para máximo ahorro de espacio |
| `showToolbar` | boolean | ❌ | true | 🛠️ Mostrar barra de herramientas |
| `showPagination` | boolean | ❌ | true | 📄 Mostrar paginación |
| `showBulkActions` | boolean | ❌ | true | 🎯 Mostrar acciones masivas |

### 📤 Events

| Event | Payload | Descripción |
|-------|---------|-------------|
| `update:appliedFilters` | `Record<string, string>` | 🔍 Filtros aplicados actualizados |
| `clear-all-filters` | - | 🧹 Todos los filtros limpiados |
| `export` | `ExportFormat` | 📥 Exportación iniciada |
| `update:deletedMode` | `boolean` | 🗑️ Modo de eliminación actualizado |
| `row-selected` | `Row<TData>[]` | ✅ Filas seleccionadas |
| `bulk-action` | `{ action: BulkAction, rows: Row<TData>[] }` | 🎯 Acción masiva ejecutada |

## 🎨 Ejemplos Prácticos

### 📝 Ejemplo Básico de Tareas

```vue
<script setup lang="ts">
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { DateDisplayCell } from '@/components/data-table';
import { h } from 'vue';

interface Task {
  id: number;
  title: string;
  description: string;
  status: 'pending' | 'in_progress' | 'completed';
  priority: 'low' | 'medium' | 'high';
  due_date: string;
  created_at: string;
}

const taskColumns = [
  {
    accessorKey: 'title',
    header: 'Título',
    cell: ({ row }) => row.original.title,
    size: 200
  },
  {
    accessorKey: 'status',
    header: 'Estado',
    cell: ({ row }) => {
      const status = row.original.status;
      const variants = {
        pending: 'secondary',
        in_progress: 'default',
        completed: 'success'
      };
      const labels = {
        pending: 'Pendiente',
        in_progress: 'En Progreso',
        completed: 'Completado'
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
    accessorKey: 'due_date',
    header: 'Fecha de vencimiento',
    cell: ({ row }) => h(DateDisplayCell, { value: row.original.due_date }),
    size: 150
  }
];
</script>

<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    :enableGlobalSearch="true"
    :enableColumnVisibility="true"
    :enableRowSelection="true"
    :initialPageSize="10"
    searchPlaceholder="Buscar tareas..."
  />
</template>
```

### ⚙️ Ejemplo Completo con Todas las Funcionalidades

```vue
<script setup lang="ts">
import { DataTable, type ExportConfig, type FilterConfig, type RowAction } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Edit, Trash2, Eye, Undo2, Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
  onExport: async (format) => {
    const response = await fetch(`/api/tasks/export?format=${format}`);
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `tasks-${new Date().toISOString().split('T')[0]}.${format}`;
    a.click();
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
    group: 'basic'
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
    group: 'basic'
  },
  {
    key: 'due_date',
    type: 'date-range',
    label: 'Fecha de vencimiento',
    placeholder: 'Selecciona rango...',
    group: 'advanced'
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
    order: 1
  },
  {
    label: 'Editar',
    icon: Edit,
    actionKind: 'route',
    routeName: 'tasks.edit',
    routeParams: (row) => ({ task: row.id }),
    permission: 'tasks.update',
    order: 2
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
    order: 3
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
    confirmMessage: '¿Estás seguro de que quieres restaurar esta tarea?'
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
    class: 'text-red-600'
  }
];

// Acciones masivas
const bulkActions = [
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
    variant: 'destructive'
  },
  {
    id: 'export',
    label: 'Exportar Seleccionadas',
    icon: Download,
    endpoint: '/api/tasks/bulk-export',
    method: 'POST',
    permission: 'tasks.export',
    uiBehavior: 'none',
    variant: 'outline'
  }
];

// Columnas de la tabla
const columns = [
  {
    accessorKey: 'title',
    header: 'Título',
    cell: ({ row }) => row.original.title,
    size: 200
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
    cell: ({ row }) => row.original.assigned_to,
    size: 150
  },
  {
    accessorKey: 'due_date',
    header: 'Fecha de vencimiento',
    cell: ({ row }) => h(DateDisplayCell, { value: row.original.due_date }),
    size: 150
  },
  {
    accessorKey: 'created_at',
    header: 'Creado',
    cell: ({ row }) => h(DateDisplayCell, { value: row.original.created_at }),
    size: 120
  }
];
</script>

<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="columns"
    :moduleId="'tasks'"
    :exportConfig="exportConfig"
    :customFilters="filtersConfig"
    :rowActions="rowActions"
    :deletedRowActions="deletedRowActions"
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
</template>
```

## ✅ Mejores Prácticas

### 📊 Configuración de Densidad
- 📊 Para tablas con muchos datos, usa `density="compact"`
- 🎯 Para uso general, usa `density="normal"`
- 📖 Para tablas de lectura, usa `density="comfortable"`

### 📐 Altura de Filas
- 📏 Para filas pequeñas, usa `rowHeight="sm"`
- 📐 Para filas medianas (por defecto), usa `rowHeight="md"`
- 📖 Para filas grandes, usa `rowHeight="lg"`

### 🎯 Modo Compacto
- 🎯 Para máxima eficiencia de espacio, usa `compactMode="true"`
- 📄 Para ocultar elementos opcionales, usa `showToolbar="false"`, `showPagination="false"`

### 📤 Exportación
- 📥 Configura siempre el `exportConfig` para habilitar exportación
- 🔒 Verifica permisos antes de exportar
- 📄 Usa formatos apropiados según el caso de uso

### 🗑️ Gestión de Eliminación
- 🔄 Implementa siempre el modo de eliminación para soft deletes
- 🛡️ Usa confirmaciones para acciones destructivas
- 🔒 Verifica permisos para restaurar y eliminar permanentemente

### 🔍 Filtros
- 🎯 Agrupa filtros en 'basic' y 'advanced' para mejor UX
- 📝 Proporciona placeholders descriptivos
- 🔄 Usa tipos de filtro apropiados (text, select, multiselect, date, date-range)

## 🔧 Solución de Problemas

### 1. Error de Carga
- 🔄 Verifica la conexión a la API
- 📏 Asegúrate de que el endpoint esté correcto
- 💫 Si el error persiste, revisa la consola del navegador

### 2. Filtros no Funcionales
- 🔎 Asegúrate de que las columnas sean `filterable: true`
- 🎯 Verifica que los filtros personalizados estén correctamente implementados
- 🔎 Si el filtro global no funciona, revisa la configuración de `enableGlobalSearch`

### 3. Exportación de Datos
- 📥 Asegúrate de que el `exportConfig` esté correctamente configurado
- 🔒 Verifica que los permisos de exportación estén configurados
- 📄 Asegúrate de que el endpoint de exportación sea accesible

### 4. Acciones por Fila
- ⚡ Verifica que las acciones tengan los permisos correctos
- 🔄 Asegúrate de que los endpoints de las acciones sean válidos
- 🛡️ Configura confirmaciones para acciones destructivas

### 5. Modo de Eliminación
- 🗑️ Verifica que el backend soporte soft deletes
- 🔄 Asegúrate de que los endpoints de restauración estén implementados
- 🔒 Verifica permisos para restaurar y eliminar permanentemente