<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">Nueva Tarea</h1>
          <p class="text-muted-foreground">
            Crea una nueva tarea para tu proyecto
          </p>
        </div>
        <Button variant="outline" @click="$router.back()">
          Volver
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Información de la Tarea</CardTitle>
          <CardDescription>
            Completa los campos para crear una nueva tarea
          </CardDescription>
        </CardHeader>
        <CardContent>
          <TaskForm @saved="handleTaskCreated" @cancel="handleCancel" />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import TaskForm from '@/components/TaskForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import type { BreadcrumbItemType } from '@/types';

interface Task {
  id: string;
  title: string;
  description?: string;
  status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high';
  assigned_to?: string;
  due_date?: string;
  created_at: string;
  updated_at: string;
}

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
  {
    title: 'Nueva Tarea',
    href: '/tasks/create',
  },
];

const handleTaskCreated = (task: Task) => {
  // Redirect to tasks list after creation
  router.visit('/tasks');
};

const handleCancel = () => {
  router.visit('/tasks');
};
</script> 