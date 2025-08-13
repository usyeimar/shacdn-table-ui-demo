<template>
  <Card>
    <!-- Header -->
    <CardHeader>
      <div class="flex items-center justify-between">
        <div>
          <CardTitle>{{ task.title }}</CardTitle>
          <CardDescription>
            Creada el {{ formatDate(task.created_at) }}
          </CardDescription>
        </div>
        <div class="flex items-center space-x-2">
          <Button
            variant="outline"
            size="sm"
            @click="$emit('edit', task)"
          >
            Editar
          </Button>
          <Button
            variant="destructive"
            size="sm"
            @click="showDeleteConfirm = true"
          >
            Eliminar
          </Button>
        </div>
      </div>
    </CardHeader>

    <!-- Content -->
    <CardContent class="space-y-6">
      <!-- Estado y Prioridad -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label class="text-sm font-medium text-muted-foreground mb-2">Estado</Label>
          <Badge :variant="getStatusVariant(task.status)">
            {{ getStatusLabel(task.status) }}
          </Badge>
        </div>
        <div>
          <Label class="text-sm font-medium text-muted-foreground mb-2">Prioridad</Label>
          <Badge :variant="getPriorityVariant(task.priority)">
            {{ getPriorityLabel(task.priority) }}
          </Badge>
        </div>
      </div>

      <!-- Descripción -->
      <div v-if="task.description">
        <Label class="text-sm font-medium text-muted-foreground mb-2">Descripción</Label>
        <p class="text-sm text-foreground whitespace-pre-wrap">
          {{ task.description }}
        </p>
      </div>

      <!-- Información adicional -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Asignado a -->
        <div>
          <Label class="text-sm font-medium text-muted-foreground mb-2">Asignado a</Label>
          <p class="text-sm text-foreground">
            {{ task.assigned_user?.name || 'Sin asignar' }}
          </p>
        </div>

        <!-- Fecha de vencimiento -->
        <div>
          <Label class="text-sm font-medium text-muted-foreground mb-2">Fecha de Vencimiento</Label>
          <p :class="isOverdue ? 'text-destructive font-medium' : 'text-foreground'">
            {{ task.due_date ? formatDate(task.due_date) : 'Sin fecha' }}
            <Badge v-if="isOverdue" variant="destructive" class="ml-2">Vencida</Badge>
          </p>
        </div>
      </div>

      <!-- Fecha de completado -->
      <div v-if="task.completed_at">
        <Label class="text-sm font-medium text-muted-foreground mb-2">Completada el</Label>
        <p class="text-sm text-foreground">
          {{ formatDate(task.completed_at) }}
        </p>
      </div>

      <!-- Última actualización -->
      <div>
        <Label class="text-sm font-medium text-muted-foreground mb-2">Última actualización</Label>
        <p class="text-sm text-muted-foreground">
          {{ formatDate(task.updated_at) }}
        </p>
      </div>
    </CardContent>

    <!-- Acciones rápidas -->
    <CardFooter class="bg-muted/50 border-t">
      <div class="flex flex-wrap gap-2">
        <Button
          v-if="task.status !== 'completed'"
          @click="markAsCompleted"
          :disabled="isLoading"
          variant="default"
          size="sm"
        >
          Marcar como Completada
        </Button>
        <Button
          v-if="task.status === 'completed'"
          @click="markAsInProgress"
          :disabled="isLoading"
          variant="outline"
          size="sm"
        >
          Marcar como En Progreso
        </Button>
        <Button
          v-if="task.status !== 'cancelled'"
          @click="markAsCancelled"
          :disabled="isLoading"
          variant="destructive"
          size="sm"
        >
          Cancelar Tarea
        </Button>
      </div>
    </CardFooter>
  </Card>

  <!-- Modal de confirmación de eliminación -->
  <Dialog v-model:open="showDeleteConfirm">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogDescription>
          ¿Estás seguro de que quieres eliminar esta tarea? Esta acción no se puede deshacer.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button
          variant="outline"
          @click="showDeleteConfirm = false"
        >
          Cancelar
        </Button>
        <Button
          variant="destructive"
          @click="deleteTask"
          :disabled="isLoading"
        >
          Eliminar
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import axios from 'axios';

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
  completed_at?: string;
  created_at: string;
  updated_at: string;
}

interface Props {
  task: Task;
}

interface Emits {
  (e: 'edit', task: Task): void;
  (e: 'deleted'): void;
  (e: 'updated', task: Task): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const showDeleteConfirm = ref(false);
const isLoading = ref(false);

const isOverdue = computed(() => {
  if (!props.task.due_date || props.task.status === 'completed') {
    return false;
  }
  return new Date(props.task.due_date) < new Date();
});

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

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

const getPriorityLabel = (priority: string) => {
  const labels = {
    low: 'Baja',
    medium: 'Media',
    high: 'Alta'
  };
  return labels[priority] || priority;
};

const getPriorityVariant = (priority: string) => {
  const variants = {
    low: 'secondary',
    medium: 'outline',
    high: 'destructive'
  };
  return variants[priority] || 'outline';
};

const markAsCompleted = async () => {
  isLoading.value = true;
  try {
    const response = await axios.post(`/api/tasks/${props.task.id}/mark-completed`);
    emit('updated', response.data.data);
  } catch (error) {
    console.error('Error marking task as completed:', error);
  } finally {
    isLoading.value = false;
  }
};

const markAsInProgress = async () => {
  isLoading.value = true;
  try {
    const response = await axios.patch(`/api/tasks/${props.task.id}`, {
      status: 'in_progress',
      completed_at: null
    });
    emit('updated', response.data.data);
  } catch (error) {
    console.error('Error marking task as in progress:', error);
  } finally {
    isLoading.value = false;
  }
};

const markAsCancelled = async () => {
  isLoading.value = true;
  try {
    const response = await axios.patch(`/api/tasks/${props.task.id}`, {
      status: 'cancelled'
    });
    emit('updated', response.data.data);
  } catch (error) {
    console.error('Error marking task as cancelled:', error);
  } finally {
    isLoading.value = false;
  }
};

const deleteTask = async () => {
  isLoading.value = true;
  try {
    await axios.delete(`/api/tasks/${props.task.id}`);
    showDeleteConfirm.value = false;
    emit('deleted');
  } catch (error) {
    console.error('Error deleting task:', error);
  } finally {
    isLoading.value = false;
  }
};
</script> 