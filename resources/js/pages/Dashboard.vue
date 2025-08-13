<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TaskForm from '@/components/TaskForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { CheckSquare, Plus, BarChart3 } from 'lucide-vue-next';
import axios from 'axios';
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
}

const recentTasks = ref<Task[]>([]);
const showCreateTaskModal = ref(false);

// Breadcrumbs
const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

const getStatusLabel = (status: string) => {
  const labels = {
    pending: 'Pendiente',
    in_progress: 'En Progreso',
    completed: 'Completada',
    cancelled: 'Cancelada'
  };
  return labels[status] || status;
};

const getStatusVariant = (status: string) => {
  const variants = {
    pending: 'secondary',
    in_progress: 'default',
    completed: 'default',
    cancelled: 'destructive'
  };
  return variants[status] || 'secondary';
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const loadRecentTasks = async () => {
  try {
    const response = await axios.get('/api/tasks?limit=5');
    recentTasks.value = response.data.data || [];
  } catch (error) {
    console.error('Error loading recent tasks:', error);
  }
};

const handleTaskCreated = (task: Task) => {
  showCreateTaskModal.value = false;
  loadRecentTasks();
};

onMounted(() => {
  loadRecentTasks();
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="mb-8">
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <p class="text-muted-foreground">
          Bienvenido al sistema de gestión de tareas
        </p>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <Card>
          <CardHeader>
            <div class="flex items-center space-x-2">
              <CheckSquare class="h-5 w-5 text-primary" />
              <CardTitle class="text-lg">Gestión de Tareas</CardTitle>
            </div>
            <CardDescription>Administra todas tus tareas</CardDescription>
          </CardHeader>
          <CardContent>
            <Link :href="route('web.tasks.index')">
              <Button variant="outline" class="w-full">
                Ver Tareas
              </Button>
            </Link>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div class="flex items-center space-x-2">
              <Plus class="h-5 w-5 text-primary" />
              <CardTitle class="text-lg">Nueva Tarea</CardTitle>
            </div>
            <CardDescription>Crear una nueva tarea</CardDescription>
          </CardHeader>
          <CardContent>
            <Button @click="showCreateTaskModal = true" class="w-full">
              Crear Tarea
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div class="flex items-center space-x-2">
              <BarChart3 class="h-5 w-5 text-primary" />
              <CardTitle class="text-lg">Estadísticas</CardTitle>
            </div>
            <CardDescription>Ver reportes y métricas</CardDescription>
          </CardHeader>
          <CardContent>
            <Link :href="route('web.tasks.index')">
              <Button variant="outline" class="w-full">
                Ver Estadísticas
              </Button>
            </Link>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Tasks -->
      <Card>
        <CardHeader>
          <CardTitle>Tareas Recientes</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="recentTasks.length === 0" class="text-center py-8">
            <CheckSquare class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium">No hay tareas</h3>
            <p class="mt-1 text-sm text-muted-foreground">Comienza creando tu primera tarea.</p>
            <div class="mt-6">
              <Button @click="showCreateTaskModal = true">
                <Plus class="mr-2 h-4 w-4" />
                Nueva Tarea
              </Button>
            </div>
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="task in recentTasks"
              :key="task.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50"
            >
              <div class="flex items-center space-x-3">
                <Badge :variant="getStatusVariant(task.status)">
                  {{ getStatusLabel(task.status) }}
                </Badge>
                <div>
                  <h4 class="text-sm font-medium">{{ task.title }}</h4>
                  <p class="text-sm text-muted-foreground">
                    {{ task.assigned_user?.name || 'Sin asignar' }}
                  </p>
                </div>
              </div>
              <div class="text-sm text-muted-foreground">
                {{ formatDate(task.created_at) }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
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
    
  </AppLayout>
</template>
