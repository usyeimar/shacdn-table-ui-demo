// src/features/roles/RolesDatatable.vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'; //
import { Plus, PlusCircleIcon, Settings2, Trash2Icon, Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { BulkAction, DefaultRowActionsConfig, PaginationMeta, RowAction, ExportConfig } from './datatable.types';
import { useAppPermissions } from './permissions';
import { getRolesColumns } from './roles.colums';
import { rolesFiltersConfig } from './roles.filters';
import type { ApiRoleItem, ApiRolesResponse, RoleData } from './roles.types';
import Datatable from './Datatable.vue';
import { useQuery } from '@tanstack/vue-query';
import axios from 'axios';
import { route } from 'ziggy-js';
import { toast } from 'vue-sonner';

const moduleId = 'roles';
const endpoint = route('api.v1.access-control.roles.index');

const { hasPermissions } = useAppPermissions();

const tableData = ref<RoleData[]>([]);

// Transformación de la respuesta de la API
const transformRoles = (jsonData: ApiRolesResponse): { data: RoleData[]; meta: PaginationMeta } => {
    const data = jsonData.data.map((item: ApiRoleItem) => ({
        id: item.id,
        name: item.name,
        description: item.description,
        guard_name: item.guard_name,
        created_at: item.created_at,
        updated_at: item.updated_at,
        permissions_count: item.permissions_count,
        users_count: item.users_count,
        mainData: {
            name: item.name,
            subtext: item.description || `Guard: ${item.guard_name}`,
        },
        routes: {
            // Rutas de Inertia para acciones de fila
            editRoute: route('admin.access-control.roles.edit', { role: item.id }),
            destroyRoute: route('api.v1.admin.access-control.roles.destroy', { role: item.id }),
        },
    }));

    console.log();

    console.log('Transformed Roles Data:', data);
    console.log('JSON Data:', jsonData);
    return { data, meta: jsonData.meta };
};

const deletedMode = ref(false); // Control local para el modo papelera

const defaultRowActionsConfig = computed<DefaultRowActionsConfig<RoleData>>(() => ({
    normalMode: {
        edit: {
            permission: 'admin.access-control.roles.update',
            enabled: hasPermissions('admin.access-control.roles.update'),
        },
        delete: {
            permission: 'admin.access-control.roles.delete',
            enabled: hasPermissions('admin.access-control.roles.delete'),
        },
    },
    editRoute: (row) => route('admin.access-control.roles.edit', { role: row.id }),
    deleteRoute: (row) => route('api.v1.admin.access-control.roles.destroy', { role: row.id }),
}));

// No hay acciones de fila personalizadas en este ejemplo sencillo
const customRowActions = ref<RowAction<RoleData>[]>([]);

const columns = computed(() => getRolesColumns(defaultRowActionsConfig.value, customRowActions.value, deletedMode.value));


const bulkActions = computed<BulkAction<RoleData>[]>(() => {
    if (deletedMode.value) {
        return []; // No hay acciones masivas en modo papelera
    }
    return [
        {
            id: 'bulk-delete',
            label: 'Eliminar Seleccionados',
            icon: Trash2Icon,
            endpoint: route('api.v1.admin.access-control.roles.destroy', { role: '{role}' }),
            method: 'DELETE', // O POST si el backend espera un array de IDs en el body
            permission: 'admin.access-control.roles.delete', // [cite: 1271]
            confirm: true,
            confirmMessage: '¿Seguro que quieres eliminar los roles seleccionados?',
            uiBehavior: 'notifyRefresh',
            disabled: (selectedRows) => {
                console.log('Bulk action disabled check:', selectedRows);
                return selectedRows.length === 0; // Deshabilitar si no hay filas seleccionadas
            },
            variant: 'destructive', // Cambia el estilo del botón si es necesario
        },
        {
            id: 'bulk-restore',
            label: 'Restaurar Seleccionados',
            icon: PlusCircleIcon,
            endpoint: route('api.v1.admin.access-control.roles.show', { role: '{role}' }),
            method: 'POST', // O PUT si el backend espera un array de IDs en el body
            permission: 'admin.access-control.roles.restore', // [cite: 1271]
            confirm: true,
            confirmMessage: '¿Seguro que quieres restaurar los roles seleccionados?',
            uiBehavior: 'notifyRefresh',
            disabled: (selectedRows) => {
                console.log('Bulk action disabled check:', selectedRows);
                return selectedRows.length === 0; // Deshabilitar si no hay filas seleccionadas
            },
            variant: 'secondary', // Cambia el estilo del botón si es necesario
        },
    ];
});

const exportConfig = computed<ExportConfig>(() => ({
    formats: ['csv', 'xlsx', 'pdf', 'json'],
    endpoint: route('api.v1.admin.access-control.roles.store'),
    filename: `roles-export-${new Date().toISOString().split('T')[0]}`,
    onExport: async (format) => {
        try {
            const response = await axios.get(route('api.v1.admin.access-control.roles.store'), {
                params: {
                    format,
                    ...(deletedMode.value ? { trashed: true } : {})
                },
                responseType: 'blob'
            });

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `${exportConfig.value.filename}.${format}`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            toast.success('Exportación completada');
        } catch (error) {
            console.error('Error exporting roles:', error);
            toast.error('Error al exportar los roles');
            throw error;
        }
    }
}));

</script>

<template>
    <div class="w-full space-y-4 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Gestión de Roles</h1>
            <div class="flex items-center gap-2">
                <Button v-if="hasPermissions('access-control.roles.create')" @click="router.visit(route('access-control.roles.create'))">
                    <PlusCircleIcon class="mr-2 h-4 w-4" />
                    Nuevo Rol
                </Button>
            </div>
        </div>

        <Datatable
            :endpoint="endpoint"
            :columns="columns"
            :transformFn="transformRoles"
            :moduleId="moduleId"
            :initialPageSize="10"
            :customRowActions="customRowActions"
            :defaultRowActionsConfig="defaultRowActionsConfig"
            :bulkActions="bulkActions"
            :customFilters="rolesFiltersConfig"
            :deletedMode="deletedMode"
            :exportConfig="exportConfig"
            searchPlaceholder="Buscar roles..."
        >
            <template #empty-state>
                <div class="flex flex-col items-center justify-center py-8">
                    <Settings2 class="text-muted-foreground mb-2 h-12 w-12" />
                    <p class="text-muted-foreground">No se encontraron roles</p>
                    <Button variant="link" class="mt-2" @click="router.visit(route('access-control.roles.create'))">
                        <Plus class="mr-2 h-4 w-4" />
                        Crear nuevo rol
                    </Button>
                </div>
            </template>
        </Datatable>
    </div>
</template>
