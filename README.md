# 📋 Módulo de Tareas

Este módulo proporciona una API completa para la gestión de tareas con funcionalidades avanzadas de CRUD, filtrado,
exportación y gestión de eliminación.

## 🏗️ Estructura del Módulo

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── Core/
│   │           └── Task/
│   │               ├── TasksController.php
│   │               └── Docs/
│   │                   └── TasksControllerDocs.php
│   ├── Requests/
│   │   └── Core/
│   │       └── Task/
│   │           ├── StoreTasksRequest.php
│   │           ├── UpdateTasksRequest.php
│   │           ├── BulkActionRequest.php
│   │           └── ExportRequest.php
│   └── Resources/
│       └── Core/
│           └── Task/
│               └── TaskResource.php
├── Models/
│   └── Core/
│       └── Task/
│           └── Task.php
├── Services/
│   └── Core/
│       └── Task/
│           └── TasksService.php
└── database/
    ├── migrations/
    │   └── 2024_01_01_000000_create_tasks_table.php
    └── seeders/
        └── TaskSeeder.php
```

## 🚀 Características

### ✅ Funcionalidades Principales

- **CRUD completo** de tareas
- **Soft deletes** con restauración
- **Filtrado avanzado** por múltiples criterios
- **Búsqueda global** en título, descripción y usuario asignado
- **Ordenamiento** por cualquier campo
- **Paginación** configurable
- **Exportación** en múltiples formatos (CSV, Excel, PDF, JSON)
- **Acciones masivas** (eliminar, restaurar, archivar)
- **Estadísticas** en tiempo real
- **Auditoría** completa (creado por, actualizado por)

### 🔐 Control de Acceso

- **Permisos granulares** usando Spatie Laravel Permission
- **Autorización** en cada operación
- **Validación** robusta con Form Requests
- **Sanitización** de datos de entrada

### 📊 Estados y Prioridades

- **Estados**: `pending`, `in_progress`, `completed`, `cancelled`, `archived`
- **Prioridades**: `low`, `medium`, `high`
- **Scopes** predefinidos para consultas comunes
- **Accessors** para etiquetas y colores

## 🛠️ Instalación y Configuración

### 1. Ejecutar Migración

```bash
php artisan migrate
```

### 2. Ejecutar Seeder

```bash
php artisan db:seed --class=TaskSeeder
```

### 3. Configurar Permisos

```bash
# Crear permisos básicos
php artisan permission:create-permission tasks.read
php artisan permission:create-permission tasks.create
php artisan permission:create-permission tasks.update
php artisan permission:create-permission tasks.delete
php artisan permission:create-permission tasks.restore
php artisan permission:create-permission tasks.forceDelete
php artisan permission:create-permission tasks.archive
php artisan permission:create-permission tasks.export
```

## 📡 Endpoints de la API

### 🔍 Consultas Básicas

```http
GET /api/v1/tasks                    # Listar tareas con paginación
GET /api/v1/tasks/{id}              # Obtener tarea específica
GET /api/v1/tasks/deleted           # Listar tareas eliminadas
GET /api/v1/tasks/stats             # Obtener estadísticas
GET /api/v1/tasks/statuses          # Obtener estados disponibles
GET /api/v1/tasks/priorities        # Obtener prioridades disponibles
```

### ✏️ Operaciones CRUD

```http
POST /api/v1/tasks                  # Crear nueva tarea
PATCH /api/v1/tasks/{id}           # Actualizar tarea
DELETE /api/v1/tasks/{id}          # Eliminar tarea (soft delete)
POST /api/v1/tasks/{id}/restore    # Restaurar tarea eliminada
DELETE /api/v1/tasks/{id}/force-delete # Eliminar permanentemente
```

### 🎯 Operaciones Específicas

```http
POST /api/v1/tasks/{id}/archive    # Archivar tarea
POST /api/v1/tasks/{id}/mark-completed # Marcar como completada
POST /api/v1/tasks/{id}/assign     # Asignar a usuario
POST /api/v1/tasks/{id}/priority   # Actualizar prioridad
```

### 📦 Acciones Masivas

```http
POST /api/v1/tasks/bulk-delete      # Eliminar múltiples tareas
POST /api/v1/tasks/bulk-restore     # Restaurar múltiples tareas
POST /api/v1/tasks/bulk-force-delete # Eliminar permanentemente múltiples
POST /api/v1/tasks/bulk-archive     # Archivar múltiples tareas
```

### 📤 Exportación

```http
POST /api/v1/tasks/export          # Exportar tareas
```

## 🔍 Filtros Disponibles

### Parámetros de Consulta

- `search` - Búsqueda en título, descripción y usuario asignado
- `status` - Filtro por estado (múltiple)
- `priority` - Filtro por prioridad (múltiple)
- `assigned_to` - Filtro por usuario asignado
- `due_date_start` - Fecha de vencimiento desde
- `due_date_end` - Fecha de vencimiento hasta
- `created_between` - Rango de fechas de creación
- `overdue` - Tareas vencidas
- `due_today` - Tareas que vencen hoy
- `sort` - Campo de ordenamiento
- `direction` - Dirección de ordenamiento (asc/desc)
- `per_page` - Elementos por página

### Ejemplo de Consulta

```http
GET /api/v1/tasks?status[]=pending&status[]=in_progress&priority=high&search=urgente&sort=due_date&direction=asc&per_page=20
```

## 📊 Formato de Respuesta

### Lista de Tareas

```json
{
    "data": [
        {
            "id": 1,
            "title": "Implementar autenticación",
            "description": "Configurar Laravel Sanctum...",
            "status": {
                "value": "completed",
                "label": "Completado",
                "color": "green"
            },
            "priority": {
                "value": "high",
                "label": "Alta",
                "color": "red"
            },
            "assigned_user_name": "Juan Pérez",
            "due_date": "2024-01-15T10:00:00.000000Z",
            "due_date_formatted": "15/01/2024 10:00",
            "is_overdue": false,
            "is_due_today": false,
            "can_edit": true,
            "can_delete": true
        }
    ],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Estadísticas

```json
{
    "success": true,
    "message": "Estadísticas obtenidas exitosamente",
    "data": {
        "total": 75,
        "pending": 25,
        "in_progress": 15,
        "completed": 30,
        "cancelled": 3,
        "overdue": 5,
        "due_today": 2,
        "deleted": 8
    }
}
```

## 🔧 Uso con DataTable

### Configuración Básica

```vue

<template>
    <DataTable
        :endpoint="'/api/v1/tasks'"
        :columns="taskColumns"
        :moduleId="'tasks'"
        :enableGlobalSearch="true"
        :enableColumnVisibility="true"
        :enableRowSelection="true"
    />
</template>
```

### Configuración Completa con Exportación

```vue

<script setup>
    const exportConfig = {
        formats: ['csv', 'xlsx', 'pdf'],
        endpoint: '/api/v1/tasks/export',
        filename: 'tasks-report',
        onExport: async (format) => {
            const response = await fetch(`/api/v1/tasks/export?format=${format}`);
            const blob = await response.blob();
            // Descargar archivo...
        }
    };
</script>

<template>
    <DataTable
        :endpoint="'/api/v1/tasks'"
        :columns="taskColumns"
        :moduleId="'tasks'"
        :exportConfig="exportConfig"
        :customFilters="filtersConfig"
        :rowActions="rowActions"
        :deletedRowActions="deletedRowActions"
        :bulkActions="bulkActions"
        :deletedMode="deletedMode"
        :enableDeletedModeToggle="true"
    />
</template>
```

## 🧪 Testing

### Ejecutar Tests

```bash
# Tests unitarios
php artisan test --filter=TaskTest

# Tests de integración
php artisan test --filter=TaskApiTest
```

### Datos de Prueba

El seeder incluye 15 tareas de ejemplo con diferentes estados y prioridades, más 2 tareas eliminadas para probar la
funcionalidad de restauración.

## 🔒 Seguridad

### Permisos Requeridos

- `tasks.read` - Leer tareas
- `tasks.create` - Crear tareas
- `tasks.update` - Actualizar tareas
- `tasks.delete` - Eliminar tareas
- `tasks.restore` - Restaurar tareas eliminadas
- `tasks.forceDelete` - Eliminar permanentemente
- `tasks.archive` - Archivar tareas
- `tasks.export` - Exportar tareas

### Validación

- Validación de entrada con Form Requests
- Sanitización de datos
- Verificación de permisos en cada operación
- Protección contra CSRF
- Rate limiting configurado

## 📈 Rendimiento

### Optimizaciones Implementadas

- **Eager loading** de relaciones
- **Índices** en campos frecuentemente consultados
- **Scopes** para consultas comunes
- **Paginación** para grandes volúmenes
- **Caching** de estadísticas
- **Optimización** de consultas N+1

### Monitoreo

- Logging de operaciones críticas
- Métricas de rendimiento
- Alertas para operaciones lentas

## 🚀 Próximas Mejoras

- [ ] Notificaciones en tiempo real
- [ ] Adjuntos de archivos
- [ ] Comentarios en tareas
- [ ] Dependencias entre tareas
- [ ] Plantillas de tareas
- [ ] Automatización de flujos de trabajo
- [ ] Integración con calendarios
- [ ] Reportes avanzados
- [ ] API webhooks
- [ ] Importación masiva
