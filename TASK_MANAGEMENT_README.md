# 🎯 Sistema de Gestión de Tareas

Un sistema completo de gestión de tareas construido con Laravel, Vue.js y el componente DataTable personalizado.

## 📋 Características Principales

### ✨ Funcionalidades del Sistema
- **CRUD Completo**: Crear, leer, actualizar y eliminar tareas
- **Gestión de Estados**: Pendiente, En Progreso, Completada, Cancelada
- **Sistema de Prioridades**: Baja, Media, Alta
- **Asignación de Usuarios**: Asignar tareas a usuarios específicos
- **Fechas de Vencimiento**: Control de fechas límite con alertas de vencimiento
- **Soft Delete**: Eliminación suave con posibilidad de restauración
- **Exportación de Datos**: Exportar tareas en múltiples formatos
- **Búsqueda y Filtrado**: Búsqueda global y filtros avanzados
- **Acciones Masivas**: Operaciones en lote para múltiples tareas

### 🎨 Interfaz de Usuario
- **Diseño Responsivo**: Funciona en dispositivos móviles y desktop
- **Tema Oscuro/Claro**: Soporte para ambos temas
- **Componentes Reutilizables**: Arquitectura modular y escalable
- **DataTable Avanzado**: Tabla de datos con funcionalidades avanzadas

## 🚀 Instalación y Configuración

### Prerrequisitos
- PHP 8.1+
- Laravel 10+
- Node.js 16+
- MySQL/PostgreSQL

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd shacdn-ui-demo
```

2. **Instalar dependencias de PHP**
```bash
composer install
```

3. **Instalar dependencias de Node.js**
```bash
npm install
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos**
```bash
# Editar .env con credenciales de base de datos
php artisan migrate
php artisan db:seed
```

6. **Compilar assets**
```bash
npm run dev
```

## 📁 Estructura del Proyecto

### Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Task/
│   │       └── TasksController.php    # Controlador principal de tareas
│   ├── Requests/
│   │   └── Task/                      # Validaciones de formularios
│   └── Resource/
│       └── TaskResource.php           # Transformación de datos
├── Models/
│   └── Task.php                       # Modelo de tareas
└── Services/
    └── Task/
        └── TasksService.php           # Lógica de negocio
```

### Frontend (Vue.js)

```
resources/js/
├── components/
│   ├── data-table/                    # Componente DataTable principal
│   ├── TaskForm.vue                   # Formulario de tareas
│   ├── TaskDetail.vue                 # Vista detallada de tareas
│   └── Navigation.vue                 # Navegación principal
├── pages/
│   ├── Dashboard.vue                  # Dashboard principal
│   └── Tasks.vue                      # Página de gestión de tareas
└── layouts/
    └── MainLayout.vue                 # Layout principal
```

## 🎯 Uso del Sistema

### 1. Dashboard Principal
- **Acceso**: `/` o `/dashboard`
- **Funcionalidades**:
  - Vista general de tareas recientes
  - Acceso rápido a crear nuevas tareas
  - Enlaces a gestión completa de tareas
  - Estadísticas básicas

### 2. Gestión de Tareas
- **Acceso**: `/tasks`
- **Funcionalidades**:
  - Tabla de datos con todas las tareas
  - Búsqueda y filtrado avanzado
  - Acciones por fila (editar, eliminar, etc.)
  - Acciones masivas
  - Exportación de datos

### 3. Crear/Editar Tareas
- **Acceso**: Modal desde Dashboard o botón "Nueva Tarea"
- **Campos**:
  - Título (requerido)
  - Descripción (opcional)
  - Estado (Pendiente, En Progreso, Completada, Cancelada)
  - Prioridad (Baja, Media, Alta)
  - Asignado a (usuario específico)
  - Fecha de vencimiento (opcional)

## 🔧 API Endpoints

### Tareas
```bash
# Obtener todas las tareas (con paginación y filtros)
GET /api/tasks

# Crear nueva tarea
POST /api/tasks

# Obtener tarea específica
GET /api/tasks/{id}

# Actualizar tarea
PATCH /api/tasks/{id}

# Eliminar tarea (soft delete)
DELETE /api/tasks/{id}

# Restaurar tarea eliminada
POST /api/tasks/{id}/restore

# Eliminar permanentemente
DELETE /api/tasks/{id}/force-delete

# Marcar como completada
POST /api/tasks/{id}/mark-completed

# Obtener estadísticas
GET /api/tasks/stats

# Exportar tareas
POST /api/tasks/export
```

### Usuarios
```bash
# Obtener lista de usuarios (para asignación)
GET /api/users
```

## 🎨 Componente DataTable

El sistema utiliza un componente DataTable personalizado con las siguientes características:

### Configuración Básica
```vue
<DataTable
  :endpoint="'/api/tasks'"
  :columns="taskColumns"
  :moduleId="'tasks'"
  :toolbarConfig="toolbarConfig"
  :paginationConfig="paginationConfig"
  :deletionConfig="deletionConfig"
/>
```

### Configuración de Columnas
```javascript
const taskColumns = [
  {
    accessorKey: 'title',
    header: 'Título',
    cell: ({ row }) => ({
      type: 'text',
      value: row.original.title,
      className: 'font-medium text-gray-900'
    })
  }
  // ... más columnas
];
```

### Configuración de Toolbar
```javascript
const toolbarConfig = {
  showSearch: true,
  showFilters: true,
  showExport: true,
  showBulkActions: true,
  filters: [
    {
      key: 'status',
      label: 'Estado',
      type: 'select',
      options: [
        { value: 'pending', label: 'Pendiente' },
        { value: 'completed', label: 'Completada' }
      ]
    }
  ]
};
```

## 🎯 Estados de Tareas

### Estados Disponibles
1. **Pendiente** (pending): Tarea creada pero no iniciada
2. **En Progreso** (in_progress): Tarea en desarrollo
3. **Completada** (completed): Tarea finalizada
4. **Cancelada** (cancelled): Tarea cancelada

### Prioridades
1. **Baja** (low): Prioridad mínima
2. **Media** (medium): Prioridad estándar
3. **Alta** (high): Prioridad máxima

## 🔒 Seguridad

### Autenticación
- Todas las rutas requieren autenticación
- Middleware `auth:sanctum` para API
- Middleware `auth` para rutas web

### Validación
- Validación de formularios en frontend y backend
- Sanitización de datos
- Protección CSRF

## 📊 Exportación de Datos

### Formatos Soportados
- **CSV**: Para análisis en Excel
- **Excel**: Formato .xlsx
- **PDF**: Reportes imprimibles
- **JSON**: Para integraciones

### Configuración de Exportación
```javascript
const exportConfig = {
  formats: ['csv', 'excel', 'pdf'],
  filename: 'tareas-export',
  includeHeaders: true
};
```

## 🎨 Personalización

### Temas
El sistema soporta temas claro y oscuro automáticamente:
- Detección automática de preferencia del usuario
- Cambio manual de tema
- Persistencia de preferencia

### Estilos
- Utiliza Tailwind CSS para estilos
- Componentes reutilizables
- Diseño responsive

## 🚀 Despliegue

### Producción
```bash
# Optimizar para producción
npm run build

# Cache de configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Variables de Entorno
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskmanager
DB_USERNAME=root
DB_PASSWORD=
```

## 🤝 Contribución

1. Fork el proyecto
2. Crear rama para feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🆘 Soporte

Para soporte técnico o preguntas:
- Crear un issue en GitHub
- Contactar al equipo de desarrollo
- Revisar la documentación del componente DataTable

---

**¡Disfruta gestionando tus tareas de manera eficiente! 🎉** 