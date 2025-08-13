<template>
  <div class="space-y-6">
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Título -->
      <div class="space-y-2">
        <Label for="title">Título *</Label>
        <Input
          id="title"
          v-model="form.title"
          type="text"
          required
          placeholder="Ingresa el título de la tarea"
        />
        <p v-if="errors.title" class="text-sm text-destructive">
          {{ errors.title }}
        </p>
      </div>

      <!-- Descripción -->
      <div class="space-y-2">
        <Label for="description">Descripción</Label>
        <textarea
          id="description"
          v-model="form.description"
          rows="4"
          class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          placeholder="Describe la tarea en detalle"
        ></textarea>
        <p v-if="errors.description" class="text-sm text-destructive">
          {{ errors.description }}
        </p>
      </div>

      <!-- Estado -->
      <div class="space-y-2">
        <Label for="status">Estado *</Label>
        <select
          id="status"
          v-model="form.status"
          required
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <option value="pending">Pendiente</option>
          <option value="in_progress">En Progreso</option>
          <option value="completed">Completada</option>
          <option value="cancelled">Cancelada</option>
        </select>
        <p v-if="errors.status" class="text-sm text-destructive">
          {{ errors.status }}
        </p>
      </div>

      <!-- Prioridad -->
      <div class="space-y-2">
        <Label for="priority">Prioridad *</Label>
        <select
          id="priority"
          v-model="form.priority"
          required
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <option value="low">Baja</option>
          <option value="medium">Media</option>
          <option value="high">Alta</option>
        </select>
        <p v-if="errors.priority" class="text-sm text-destructive">
          {{ errors.priority }}
        </p>
      </div>

      <!-- Asignado a -->
      <div class="space-y-2">
        <Label for="assigned_to">Asignado a</Label>
        <select
          id="assigned_to"
          v-model="form.assigned_to"
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <option value="">Seleccionar usuario</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
        <p v-if="errors.assigned_to" class="text-sm text-destructive">
          {{ errors.assigned_to }}
        </p>
      </div>

      <!-- Fecha de vencimiento -->
      <div class="space-y-2">
        <Label for="due_date">Fecha de Vencimiento</Label>
        <Input
          id="due_date"
          v-model="form.due_date"
          type="datetime-local"
        />
        <p v-if="errors.due_date" class="text-sm text-destructive">
          {{ errors.due_date }}
        </p>
      </div>

      <!-- Botones -->
      <div class="flex justify-end space-x-3 pt-4">
        <Button
          type="button"
          variant="outline"
          @click="$emit('cancel')"
        >
          Cancelar
        </Button>
        <Button
          type="submit"
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting">Guardando...</span>
          <span v-else>{{ task ? 'Actualizar' : 'Crear' }} Tarea</span>
        </Button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios';

interface User {
  id: string;
  name: string;
  email: string;
}

interface Task {
  id?: string;
  title: string;
  description?: string;
  status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high';
  assigned_to?: string;
  due_date?: string;
  created_at?: string;
  updated_at?: string;
}

interface Props {
  task?: Task | null;
}

interface Emits {
  (e: 'saved', task: Task): void;
  (e: 'cancel'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const users = ref<User[]>([]);
const isSubmitting = ref(false);
const errors = reactive<Record<string, string>>({});

const form = reactive({
  title: '',
  description: '',
  status: 'pending' as const,
  priority: 'medium' as const,
  assigned_to: '',
  due_date: ''
});

// Cargar usuarios al montar el componente
onMounted(async () => {
  try {
    const response = await axios.get('/api/users');
    users.value = response.data.data || [];
  } catch (error) {
    console.error('Error loading users:', error);
  }
});

// Observar cambios en props.task para edición
watch(() => props.task, (newTask) => {
  if (newTask) {
    form.title = newTask.title;
    form.description = newTask.description || '';
    form.status = newTask.status;
    form.priority = newTask.priority;
    form.assigned_to = newTask.assigned_to || '';
    
    // Formatear fecha para input datetime-local
    if (newTask.due_date) {
      const date = new Date(newTask.due_date);
      form.due_date = date.toISOString().slice(0, 16);
    } else {
      form.due_date = '';
    }
  } else {
    // Reset form for new task
    Object.assign(form, {
      title: '',
      description: '',
      status: 'pending',
      priority: 'medium',
      assigned_to: '',
      due_date: ''
    });
  }
}, { immediate: true });

const validateForm = (): boolean => {
  errors.title = '';
  errors.description = '';
  errors.status = '';
  errors.priority = '';
  errors.assigned_to = '';
  errors.due_date = '';

  let isValid = true;

  if (!form.title.trim()) {
    errors.title = 'El título es requerido';
    isValid = false;
  }

  if (form.title.length > 255) {
    errors.title = 'El título no puede exceder 255 caracteres';
    isValid = false;
  }

  if (form.description && form.description.length > 1000) {
    errors.description = 'La descripción no puede exceder 1000 caracteres';
    isValid = false;
  }

  if (form.due_date) {
    const dueDate = new Date(form.due_date);
    const now = new Date();
    if (dueDate < now && form.status !== 'completed') {
      errors.due_date = 'La fecha de vencimiento no puede ser en el pasado';
      isValid = false;
    }
  }

  return isValid;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;
  errors.title = '';
  errors.description = '';
  errors.status = '';
  errors.priority = '';
  errors.assigned_to = '';
  errors.due_date = '';

  try {
    const taskData = {
      title: form.title.trim(),
      description: form.description.trim() || null,
      status: form.status,
      priority: form.priority,
      assigned_to: form.assigned_to || null,
      due_date: form.due_date || null
    };

    let response;
    if (props.task?.id) {
      // Actualizar tarea existente
      response = await axios.patch(`/api/tasks/${props.task.id}`, taskData);
    } else {
      // Crear nueva tarea
      response = await axios.post('/api/tasks', taskData);
    }

    emit('saved', response.data.data);
  } catch (error: any) {
    if (error.response?.data?.errors) {
      Object.keys(error.response.data.errors).forEach(key => {
        errors[key] = error.response.data.errors[key][0];
      });
    } else {
      console.error('Error saving task:', error);
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script> 