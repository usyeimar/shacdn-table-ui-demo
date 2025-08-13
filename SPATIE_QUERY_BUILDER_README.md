# Spatie Query Builder - Tasks API

Este documento explica la implementación de Spatie Query Builder en la API de tareas para proporcionar filtrado, ordenamiento y paginación avanzados.

## 🚀 Características Implementadas

### ✅ Filtros Disponibles

#### **Filtros Exactos**
- `status` - Estado de la tarea (pending, in_progress, completed, cancelled)
- `priority` - Prioridad (low, medium, high)
- `assigned_to` - ID del usuario asignado

#### **Filtros de Scope**
- `overdue` - Tareas vencidas
- `due_today` - Tareas que vencen hoy
- `due_soon` - Tareas que vencen en los próximos 7 días
- `trashed` - Incluir tareas eliminadas
- `only_trashed` - Solo tareas eliminadas

#### **Filtros de Callback**
- `search` - Búsqueda en título, descripción y usuario asignado
- `due_date` - Rango de fechas de vencimiento
- `created_at` - Rango de fechas de creación
- `assigned_user` - Búsqueda por nombre o email del usuario asignado

### ✅ Ordenamiento Disponible

- `id` - ID de la tarea
- `title` - Título
- `status` - Estado
- `priority` - Prioridad
- `due_date` - Fecha de vencimiento
- `created_at` - Fecha de creación
- `updated_at` - Fecha de actualización
- `assigned_user` - Usuario asignado

### ✅ Includes Disponibles

- `assignedUser` - Usuario asignado
- `createdByUser` - Usuario que creó la tarea
- `updatedByUser` - Usuario que actualizó la tarea

## 📡 Ejemplos de Uso de la API

### **Filtrado Básico**

```bash
# Filtrar por estado
GET /api/tasks?filter[status]=completed

# Filtrar por prioridad
GET /api/tasks?filter[priority]=high

# Filtrar por usuario asignado
GET /api/tasks?filter[assigned_to]=1
```

### **Filtrado Avanzado**

```bash
# Búsqueda en texto
GET /api/tasks?filter[search]=implementar

# Tareas vencidas
GET /api/tasks?filter[overdue]=true

# Tareas que vencen hoy
GET /api/tasks?filter[due_today]=true

# Tareas que vencen pronto
GET /api/tasks?filter[due_soon]=true

# Solo tareas eliminadas
GET /api/tasks?filter[only_trashed]=true

# Incluir tareas eliminadas
GET /api/tasks?filter[trashed]=true
```

### **Filtrado por Rango de Fechas**

```bash
# Rango de fechas de vencimiento
GET /api/tasks?filter[due_date]=2024-01-01,2024-12-31

# Rango de fechas de creación
GET /api/tasks?filter[created_at]=2024-01-01,2024-12-31
```

### **Ordenamiento**

```bash
# Ordenar por título ascendente
GET /api/tasks?sort=title

# Ordenar por fecha de creación descendente
GET /api/tasks?sort=-created_at

# Ordenar por prioridad y luego por fecha
GET /api/tasks?sort=priority,-due_date
```

### **Paginación**

```bash
# Página específica
GET /api/tasks?page[number]=2&page[size]=10

# Formato simple
GET /api/tasks?page=2&per_page=10
```

### **Includes (Relaciones)**

```bash
# Incluir usuario asignado
GET /api/tasks?include=assignedUser

# Incluir múltiples relaciones
GET /api/tasks?include=assignedUser,createdByUser,updatedByUser
```

### **Combinaciones**

```bash
# Filtro + Ordenamiento + Paginación + Includes
GET /api/tasks?filter[status]=pending&filter[priority]=high&sort=-created_at&page[number]=1&page[size]=20&include=assignedUser
```

## 🔧 Implementación Técnica

### **Servicio de Tareas**

```php
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedInclude;

public function index(Request $request): LengthAwarePaginator
{
    $query = QueryBuilder::for(Task::class)
        ->allowedIncludes([
            'assignedUser',
            'createdByUser', 
            'updatedByUser'
        ])
        ->allowedFilters([
            AllowedFilter::exact('status'),
            AllowedFilter::exact('priority'),
            AllowedFilter::exact('assigned_to'),
            AllowedFilter::scope('overdue'),
            AllowedFilter::scope('due_today'),
            AllowedFilter::scope('due_soon'),
            AllowedFilter::scope('trashed'),
            AllowedFilter::scope('only_trashed'),
            AllowedFilter::callback('search', function (Builder $query, $value) {
                // Búsqueda personalizada
            }),
        ])
        ->allowedSorts([
            'id',
            'title',
            'status',
            'priority',
            'due_date',
            'created_at',
            'updated_at',
        ])
        ->defaultSort('-created_at');

    return $query->paginate($pageSize);
}
```

### **Scopes del Modelo**

```php
// Modelo Task
public function scopeOverdue($query)
{
    return $query->where('due_date', '<', now())
        ->where('status', '!=', 'completed')
        ->where('status', '!=', 'cancelled');
}

public function scopeDueToday($query)
{
    return $query->whereDate('due_date', today())
        ->where('status', '!=', 'completed')
        ->where('status', '!=', 'cancelled');
}

public function scopeDueSoon($query)
{
    return $query->whereBetween('due_date', [now(), now()->addDays(7)])
        ->where('status', '!=', 'completed')
        ->where('status', '!=', 'cancelled');
}
```

## 🧪 Pruebas

### **Comando de Prueba**

```bash
# Probar filtros básicos
php artisan tinker --execute="
use App\Services\Task\TasksService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

\$user = User::first();
Auth::login(\$user);

\$service = new TasksService();

// Probar filtro de estado
\$request = new Request();
\$request->merge(['filter' => ['status' => 'completed']]);
\$result = \$service->index(\$request);
echo 'Tareas completadas: ' . \$result->total() . PHP_EOL;

// Probar filtro de prioridad
\$request2 = new Request();
\$request2->merge(['filter' => ['priority' => 'high']]);
\$result2 = \$service->index(\$request2);
echo 'Tareas alta prioridad: ' . \$result2->total() . PHP_EOL;
"
```

### **Pruebas de API**

```bash
# Probar con curl
curl -X GET "http://localhost:8000/api/tasks?filter%5Bstatus%5D=completed&sort=-created_at&page%5Bnumber%5D=1&page%5Bsize%5D=10" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

## 📊 Ventajas de Spatie Query Builder

### **1. Seguridad**
- Solo permite filtros, ordenamientos e includes explícitamente definidos
- Previene inyección SQL y ataques de manipulación de consultas

### **2. Flexibilidad**
- Filtros exactos, de scope y personalizados
- Ordenamiento múltiple
- Includes de relaciones
- Paginación automática

### **3. Mantenibilidad**
- Código limpio y organizado
- Fácil de extender con nuevos filtros
- Documentación automática de la API

### **4. Performance**
- Consultas optimizadas
- Eager loading automático
- Paginación eficiente

## 🔄 Migración desde Filtros Manuales

### **Antes (Filtros Manuales)**
```php
public function index(Request $request)
{
    $query = Task::query();
    
    if ($request->filled('status')) {
        $query->where('status', $request->get('status'));
    }
    
    if ($request->filled('priority')) {
        $query->where('priority', $request->get('priority'));
    }
    
    // ... más filtros manuales
    
    return $query->paginate(15);
}
```

### **Después (Spatie Query Builder)**
```php
public function index(Request $request)
{
    return QueryBuilder::for(Task::class)
        ->allowedFilters([
            AllowedFilter::exact('status'),
            AllowedFilter::exact('priority'),
        ])
        ->paginate(15);
}
```

## 🎯 Casos de Uso

### **1. Dashboard de Tareas**
```bash
# Tareas pendientes de alta prioridad
GET /api/tasks?filter[status]=pending&filter[priority]=high&sort=due_date&page[size]=5
```

### **2. Reporte de Tareas Vencidas**
```bash
# Tareas vencidas ordenadas por prioridad
GET /api/tasks?filter[overdue]=true&sort=priority,-due_date&include=assignedUser
```

### **3. Búsqueda de Tareas**
```bash
# Búsqueda en texto con filtros
GET /api/tasks?filter[search]=implementar&filter[status]=in_progress&sort=-created_at
```

### **4. Exportación de Datos**
```bash
# Tareas completadas en el último mes
GET /api/tasks?filter[status]=completed&filter[created_at]=2024-01-01,2024-01-31&include=assignedUser
```

## 📈 Estadísticas de Rendimiento

- **Filtros**: O(1) para filtros exactos, O(n) para búsquedas
- **Ordenamiento**: O(n log n) con índices apropiados
- **Paginación**: O(1) con LIMIT y OFFSET
- **Includes**: O(1) con eager loading

## 🔧 Configuración Adicional

### **Índices Recomendados**
```sql
-- Para mejorar el rendimiento de los filtros
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_priority ON tasks(priority);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_tasks_created_at ON tasks(created_at);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
```

### **Cache de Consultas**
```php
// Implementar cache para consultas frecuentes
$result = Cache::remember("tasks_{$cacheKey}", 300, function () use ($query) {
    return $query->paginate(15);
});
```

¡La API de tareas ahora está completamente equipada con Spatie Query Builder para un filtrado, ordenamiento y paginación potentes y seguros! 