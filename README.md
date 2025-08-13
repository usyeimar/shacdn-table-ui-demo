# 📋 Sistema de Gestión de Tareas

Un sistema completo de gestión de tareas construido con **Laravel 12**, **Inertia.js**, **Vue 3**, **TypeScript** y **Tailwind CSS**, que proporciona una interfaz moderna y funcionalidades avanzadas de CRUD, filtrado, exportación y gestión de eliminación.

## 🏗️ Estructura del Proyecto

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Task/
│   │   │   │   ├── Api/
│   │   │   │   │   ├── TasksController.php
│   │   │   │   │   └── Docs/
│   │   │   │   │       └── TasksControllerDocs.php
│   │   │   │   └── Web/
│   │   │   │       └── TaskController.php
│   │   ├── Requests/
│   │   │   └── Task/
│   │   │       ├── StoreTasksRequest.php
│   │   │       ├── UpdateTasksRequest.php
│   │   │       ├── BulkActionRequest.php
│   │   │       └── ExportRequest.php
│   │   └── Resource/
│   │       └── TaskResource.php
│   ├── Models/
│   │   ├── Task.php
│   │   ├── User.php
│   │   └── README.md
│   └── Services/
│       └── Task/
│           └── TasksService.php
├── resources/
│   └── js/
│       ├── components/
│       │   ├── data-table/          # Componente DataTable avanzado
│       │   │   ├── core/            # Componentes principales
│       │   │   ├── examples/        # Ejemplos de uso
│       │   │   ├── types/           # Tipos TypeScript
│       │   │   └── README.md        # Documentación del DataTable
│       │   ├── ui/                  # Componentes UI (shadcn/ui)
│       │   ├── Auth/                # Componentes de autenticación
│       │   ├── filters/             # Componentes de filtros
│       │   ├── AppHeader.vue        # Header principal
│       │   ├── AppSidebar.vue       # Sidebar principal
│       │   ├── AppContent.vue       # Contenido principal
│       │   ├── AppShell.vue         # Shell de la aplicación
│       │   ├── AppLogo.vue          # Logo de la aplicación
│       │   ├── AppLogoIcon.vue      # Icono del logo
│       │   ├── AppSidebarHeader.vue # Header del sidebar
│       │   ├── Breadcrumbs.vue      # Navegación de migas
│       │   ├── DeleteUser.vue       # Eliminar usuario
│       │   ├── Heading.vue          # Componente de título
│       │   ├── HeadingSmall.vue     # Título pequeño
│       │   ├── Icon.vue             # Componente de iconos
│       │   ├── InputError.vue       # Error de input
│       │   ├── NavFooter.vue        # Footer de navegación
│       │   ├── NavMain.vue          # Navegación principal
│       │   ├── NavUser.vue          # Navegación de usuario
│       │   ├── PlaceholderPattern.vue # Patrón de placeholder
│       │   ├── TextLink.vue         # Enlace de texto
│       │   ├── UserInfo.vue         # Información de usuario
│       │   ├── UserMenuContent.vue  # Contenido del menú de usuario
│       │   ├── ThemeModeSwitcher.vue # Cambiador de tema
│       │   └── AppearanceTabs.vue   # Pestañas de apariencia
│       ├── layouts/                 # Layouts de la aplicación
│       ├── pages/
│       │   ├── Dashboard.vue        # Dashboard principal
│       │   ├── Welcome.vue          # Página de bienvenida
│       │   ├── auth/                # Páginas de autenticación
│       │   ├── settings/            # Páginas de configuración
│       │   └── Tasks/
│       │       ├── Create.vue       # Crear tarea
│       │       ├── Index.vue        # Índice de tareas
│       │       └── components/      # Componentes específicos
│       │           ├── TaskForm.vue
│       │           └── TaskDetail.vue
│       ├── types/                   # Tipos TypeScript globales
│       ├── composables/             # Composables Vue
│       ├── lib/                     # Utilidades y librerías
│       ├── app.ts                   # Punto de entrada
│       └── ssr.ts                   # Configuración SSR
├── routes/
│   ├── web.php                      # Rutas web (Inertia)
│   ├── api.php                      # Rutas API
│   ├── auth.php                     # Rutas de autenticación
│   ├── settings.php                 # Rutas de configuración
│   └── console.php                  # Rutas de consola
├── database/
│   ├── migrations/                  # Migraciones de base de datos
│   ├── seeders/                     # Seeders de datos
│   ├── factories/                   # Factories para testing
│   └── database.sqlite              # Base de datos SQLite
└── tests/                           # Tests automatizados
```

## 🚀 Características

### ✅ Funcionalidades Principales

- **CRUD completo** de tareas con interfaz web moderna
- **API RESTful** para integración con otros sistemas
- **Soft deletes** con restauración y gestión de eliminación
- **Filtrado avanzado** por múltiples criterios
- **Búsqueda global** en tiempo real
- **Ordenamiento** por cualquier campo
- **Paginación** configurable
- **Exportación** en múltiples formatos (CSV, Excel, PDF, JSON)
- **Acciones masivas** (eliminar, restaurar, archivar)
- **Estadísticas** en tiempo real
- **Auditoría** completa (creado por, actualizado por)

### 🎨 Interfaz de Usuario

- **Dashboard** con resumen de tareas y acciones rápidas
- **DataTable** avanzado con densidad configurable
- **Modales** para creación y edición rápida
- **Sidebar** para detalles de tareas
- **Responsive design** para todos los dispositivos
- **Temas** claros y oscuros
- **Componentes UI** consistentes (shadcn/ui)
- **Animaciones** suaves y transiciones

### 🔐 Control de Acceso

- **Permisos granulares** usando Spatie Laravel Permission
- **Autorización** en cada operación
- **Validación** robusta con Form Requests
- **Sanitización** de datos de entrada
- **Autenticación** con Laravel Sanctum

### 📊 Estados y Prioridades

- **Estados**: `pending`, `in_progress`, `completed`, `cancelled`, `archived`
- **Prioridades**: `low`, `medium`, `high`
- **Scopes** predefinidos para consultas comunes
- **Accessors** para etiquetas y colores

## 🛠️ Instalación y Configuración

### 1. Requisitos Previos

```bash
# PHP 8.2+ y Composer
composer install

# Node.js 22+ y npm
npm install
```

### 2. Configuración del Entorno

```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Base de Datos
```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeder
php artisan db:seed --class=TaskSeeder
```

### 4. Configurar Permisos

# Crear migrations y arvhico de configuración
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

# Crear las tablas
```bash
php artisan migrate
```

# Crear permisos básicos
```bash
php artisan permission:create-permission tasks.read
php artisan permission:create-permission tasks.create
php artisan permission:create-permission tasks.update
php artisan permission:create-permission tasks.delete
php artisan permission:create-permission tasks.restore
php artisan permission:create-permission tasks.forceDelete
php artisan permission:create-permission tasks.archive
php artisan permission:create-permission tasks.export
```

### 5. Desarrollo

```bash
# Servidor de desarrollo completo (recomendado)
composer run dev

# O ejecutar por separado:
php artisan serve
npm run dev
```

### 6. Scripts Disponibles

```bash
# Desarrollo
npm run dev                    # Compilar assets en modo desarrollo
npm run build                  # Compilar assets para producción
npm run build:ssr              # Compilar para SSR

# Calidad de código
npm run format                 # Formatear código con Prettier
npm run format:check           # Verificar formato
npm run lint                   # Linting con ESLint

# Testing
composer run test              # Ejecutar tests PHP
```

## 📡 Rutas y Endpoints

### 🌐 Rutas Web (Inertia.js)

```php
// Dashboard principal
GET /dashboard                    # Dashboard con resumen de tareas

// Gestión de tareas
GET /tasks                       # Lista de tareas (DataTable)
GET /tasks/create                # Formulario de creación
GET /tasks/{taskId}              # Vista detallada de tarea
GET /tasks/{taskId}/edit         # Formulario de edición

// Autenticación
GET /login                       # Página de login
GET /register                    # Página de registro
GET /forgot-password             # Recuperar contraseña

// Configuración
GET /settings                    # Configuración del usuario
```

### 🔌 Rutas API

```http
# Consultas básicas
GET /api/tasks                   # Listar tareas con paginación
GET /api/tasks/{taskId}          # Obtener tarea específica
GET /api/tasks/deleted           # Listar tareas eliminadas
GET /api/tasks/stats             # Obtener estadísticas
GET /api/tasks/statuses          # Obtener estados disponibles
GET /api/tasks/priorities        # Obtener prioridades disponibles

# Operaciones CRUD
POST /api/tasks                  # Crear nueva tarea
PATCH /api/tasks/{taskId}        # Actualizar tarea
DELETE /api/tasks/{taskId}       # Eliminar tarea (soft delete)
POST /api/tasks/{taskId}/restore # Restaurar tarea eliminada
DELETE /api/tasks/{taskId}/force-delete # Eliminar permanentemente

# Operaciones específicas
POST /api/tasks/{taskId}/archive # Archivar tarea
POST /api/tasks/{taskId}/mark-completed # Marcar como completada
POST /api/tasks/{taskId}/assign  # Asignar a usuario
POST /api/tasks/{taskId}/priority # Actualizar prioridad

# Acciones masivas
POST /api/tasks/bulk-delete      # Eliminar múltiples tareas
POST /api/tasks/bulk-restore     # Restaurar múltiples tareas
POST /api/tasks/bulk-force-delete # Eliminar permanentemente múltiples
POST /api/tasks/bulk-archive     # Archivar múltiples tareas

# Exportación
POST /api/tasks/export           # Exportar tareas
```

## 🎯 Uso del DataTable

### 📝 Configuración Básica

```vue
<template>
  <DataTable
    :endpoint="'/api/tasks'"
    :columns="taskColumns"
    :moduleId="'tasks'"
    :enableGlobalSearch="true"
    :enableColumnVisibility="true"
    :enableRowSelection="true"
    :initialPageSize="25"
    searchPlaceholder="Buscar tareas..."
  />
</template>

<script setup lang="ts">
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { h } from 'vue';

const taskColumns = [
  {
    accessorKey: 'title',
    header: 'Título',
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
  }
];
</script>
```

### ⚙️ Configuración Avanzada

```vue
<template>
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
    :density="'normal'"
    :rowHeight="'md'"
    :showToolbar="true"
    :showPagination="true"
    :showBulkActions="true"
    @update:deletedMode="deletedMode = $event"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { DataTable, type FilterConfig, type ExportConfig } from '@/components/data-table';

const deletedMode = ref(false);

const filtersConfig: FilterConfig[] = [
  {
    key: 'status',
    label: 'Estado',
    type: 'select',
    options: [
      { value: 'pending', label: 'Pendiente' },
      { value: 'in_progress', label: 'En Progreso' },
      { value: 'completed', label: 'Completado' },
      { value: 'cancelled', label: 'Cancelado' }
    ],
    multiple: true
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
    multiple: true
  }
];

const exportConfig: ExportConfig = {
  formats: ['csv', 'xlsx', 'pdf'],
  endpoint: '/api/tasks/export',
  filename: 'tasks-export',
  onExport: async (format) => {
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
```

### 🎛️ Control de Densidad

```vue
<template>
  <!-- Tabla compacta para máxima eficiencia -->
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

## 🔍 Filtros Disponibles

### Parámetros de Consulta API

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
GET /api/tasks?status[]=pending&status[]=in_progress&priority=high&search=urgente&sort=due_date&direction=asc&per_page=20
```

## 📊 Formato de Respuesta API

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

## 🧪 Testing

### Ejecutar Tests

```bash
# Tests unitarios
php artisan test --filter=TaskTest

# Tests de integración
php artisan test --filter=TaskApiTest

# Tests de frontend
npm run test
```

### Datos de Prueba

El seeder incluye 15 tareas de ejemplo con diferentes estados y prioridades, más 2 tareas eliminadas para probar la funcionalidad de restauración.

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
- **Lazy loading** de componentes
- **Code splitting** en el frontend
- **Vite** para compilación rápida
- **TanStack Query** para cache de datos

### Monitoreo

- Logging de operaciones críticas
- Métricas de rendimiento
- Alertas para operaciones lentas

## 🚀 Despliegue

### Producción

```bash
# Optimizar para producción
composer install --optimize-autoloader --no-dev
npm run build

# Configurar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configurar permisos
chmod -R 755 storage bootstrap/cache
```

### Variables de Entorno de Producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Docker (Opcional)

```dockerfile
# Dockerfile.example
FROM php:8.2-fpm
# ... configuración del contenedor
```

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 12** - Framework PHP
- **Inertia.js** - SPA sin API
- **Laravel Sanctum** - Autenticación API
- **Spatie Query Builder** - Filtrado avanzado
- **Spatie Laravel Permission** - Gestión de permisos

### Frontend
- **Vue 3** - Framework JavaScript
- **TypeScript** - Tipado estático
- **Inertia.js** - Integración con Laravel
- **TanStack Table** - Tabla de datos
- **TanStack Query** - Gestión de estado
- **Tailwind CSS** - Framework CSS
- **shadcn/ui** - Componentes UI
- **Lucide Icons** - Iconografía

### Herramientas de Desarrollo
- **Vite** - Bundler y dev server
- **ESLint** - Linting JavaScript
- **Prettier** - Formateo de código
- **Pest** - Testing PHP
- **Vue Test Utils** - Testing Vue

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
- [ ] Dashboard personalizable
- [ ] Filtros guardados
- [ ] Exportación programada
- [ ] Integración con sistemas externos
- [ ] Modo offline
- [ ] PWA (Progressive Web App)

## 📚 Documentación Adicional

- [DataTable Component](./resources/js/components/data-table/README.md) - Documentación completa del componente DataTable
- [Análisis de Filtros](./ANALISIS_FILTROS_CORREGIDOS.md) - Análisis detallado del sistema de filtros
- [Spatie Query Builder](./SPATIE_QUERY_BUILDER_README.md) - Documentación del query builder
- [Task Factory](./TASK_FACTORY_README.md) - Documentación de la factory de tareas
- [Task Management](./TASK_MANAGEMENT_README.md) - Guía de gestión de tareas

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guías de Contribución

- Sigue las convenciones de código (ESLint + Prettier)
- Escribe tests para nuevas funcionalidades
- Documenta cambios importantes
- Usa commits semánticos

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🆘 Soporte

Para soporte técnico o preguntas:
- Crear un issue en GitHub
- Contactar al equipo de desarrollo
- Revisar la documentación del componente DataTable

---

**¡Disfruta gestionando tus tareas de manera eficiente! 🎉**
