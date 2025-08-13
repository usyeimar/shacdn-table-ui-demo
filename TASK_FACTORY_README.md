# Task Factory y Seeder

Este documento explica cómo usar el factory y seeder de tareas para generar datos de prueba.

## 📋 Factory de Tareas

El `TaskFactory` se encuentra en `database/factories/TaskFactory.php` y genera datos realistas para las tareas.

### Características del Factory

- **Títulos**: Oraciones aleatorias de 3-6 palabras
- **Descripciones**: Párrafos aleatorios de 2-4 oraciones
- **Estados**: `pending`, `in_progress`, `completed`, `cancelled`
- **Prioridades**: `low`, `medium`, `high`
- **Fechas**: Distribuidas entre 3 meses atrás y 2 meses en el futuro
- **Asignaciones**: Usuarios aleatorios de la base de datos

### Estados Especiales del Factory

```php
// Tareas completadas
Task::factory(10)->completed()->create();

// Tareas en progreso
Task::factory(10)->inProgress()->create();

// Tareas canceladas
Task::factory(10)->cancelled()->create();

// Tareas de alta prioridad
Task::factory(10)->highPriority()->create();

// Tareas vencidas
Task::factory(10)->overdue()->create();

// Tareas que vencen pronto (7 días)
Task::factory(10)->dueSoon()->create();
```

## 🌱 Seeder de Tareas

El `TaskSeeder` se encuentra en `database/seeders/TaskSeeder.php` y crea una distribución realista de tareas.

### Ejecutar el Seeder

```bash
# Ejecutar solo el seeder de tareas
php artisan db:seed --class=TaskSeeder

# Ejecutar todos los seeders
php artisan db:seed
```

### Distribución por Defecto

- **30 tareas completadas** (30%)
- **25 tareas en progreso** (25%)
- **35 tareas pendientes** (35%)
- **5 tareas canceladas** (5%)
- **5 tareas vencidas de alta prioridad**
- **5 tareas que vencen pronto**
- **3 tareas eliminadas** (para pruebas)

## 🚀 Comando Personalizado

Se ha creado un comando Artisan personalizado para generar tareas de forma más flexible.

### Uso Básico

```bash
# Generar 100 tareas con distribución por defecto
php artisan tasks:generate

# Generar 50 tareas
php artisan tasks:generate --count=50

# Limpiar tareas existentes y generar nuevas
php artisan tasks:generate --clear
```

### Opciones Avanzadas

```bash
# Personalizar distribución
php artisan tasks:generate \
  --count=200 \
  --completed=40 \
  --in-progress=30 \
  --pending=20 \
  --cancelled=10 \
  --overdue=10 \
  --due-soon=10 \
  --clear
```

### Opciones Disponibles

| Opción | Descripción | Valor por Defecto |
|--------|-------------|-------------------|
| `--count` | Número total de tareas | 100 |
| `--clear` | Limpiar tareas existentes | false |
| `--completed` | Porcentaje de tareas completadas | 30 |
| `--in-progress` | Porcentaje de tareas en progreso | 25 |
| `--pending` | Porcentaje de tareas pendientes | 35 |
| `--cancelled` | Porcentaje de tareas canceladas | 5 |
| `--overdue` | Número de tareas vencidas | 5 |
| `--due-soon` | Número de tareas que vencen pronto | 5 |

## 📊 Ejemplos de Uso

### Generar Datos para Desarrollo

```bash
# Generar 100 tareas para desarrollo
php artisan tasks:generate --count=100 --clear

# Generar 500 tareas para pruebas de rendimiento
php artisan tasks:generate --count=500 --clear
```

### Generar Datos para Demostración

```bash
# Generar tareas con más tareas completadas
php artisan tasks:generate \
  --count=50 \
  --completed=50 \
  --in-progress=30 \
  --pending=15 \
  --cancelled=5 \
  --clear
```

### Generar Datos para Pruebas

```bash
# Generar tareas con muchas vencidas para probar alertas
php artisan tasks:generate \
  --count=100 \
  --overdue=20 \
  --due-soon=15 \
  --clear
```

## 🔧 Personalización

### Modificar el Factory

Para agregar nuevos estados o modificar la generación de datos, edita `database/factories/TaskFactory.php`:

```php
// Agregar nuevo estado
public function urgent(): static
{
    return $this->state(fn (array $attributes) => [
        'priority' => 'high',
        'due_date' => fake()->dateTimeBetween('now', '+3 days'),
    ]);
}
```

### Modificar el Seeder

Para cambiar la distribución por defecto, edita `database/seeders/TaskSeeder.php`:

```php
// Cambiar distribución
Task::factory(40)->completed()->create();  // Más completadas
Task::factory(20)->inProgress()->create(); // Menos en progreso
```

## 📈 Estadísticas

Para ver estadísticas de las tareas generadas:

```bash
# Ver conteo total
php artisan tinker --execute="echo 'Total: ' . App\Models\Task::count();"

# Ver distribución por estado
php artisan tinker --execute="
echo 'Completed: ' . App\Models\Task::where('status', 'completed')->count() . PHP_EOL;
echo 'In Progress: ' . App\Models\Task::where('status', 'in_progress')->count() . PHP_EOL;
echo 'Pending: ' . App\Models\Task::where('status', 'pending')->count() . PHP_EOL;
echo 'Cancelled: ' . App\Models\Task::where('status', 'cancelled')->count() . PHP_EOL;
echo 'Overdue: ' . App\Models\Task::overdue()->count() . PHP_EOL;
"
```

## 🧹 Limpieza

Para limpiar todas las tareas:

```bash
# Usando el comando personalizado
php artisan tasks:generate --count=0 --clear

# Usando Tinker
php artisan tinker --execute="App\Models\Task::truncate();"
```

## 🎯 Casos de Uso

1. **Desarrollo**: Generar datos realistas para probar la interfaz
2. **Testing**: Crear datasets específicos para pruebas automatizadas
3. **Demo**: Generar datos para presentaciones y demostraciones
4. **Performance**: Crear grandes volúmenes de datos para pruebas de rendimiento
5. **QA**: Generar casos edge como tareas vencidas o sin asignar 