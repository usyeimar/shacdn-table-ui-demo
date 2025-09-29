<script setup lang="ts" generic="TData extends Identifiable">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useConfirmationDialog } from '@/composables/useConfirmationDialog';
import { usePermissions } from '@/composables/usePermissions';
import type { Row, Table } from '@tanstack/vue-table';
import axios from 'axios';
import { Edit, ExternalLink, Eye, MoreVertical, RotateCcw, ShieldAlert, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { DefaultRowActionsConfig, Identifiable, RowAction } from '../index';
import type { DefaultRowActionConfig } from '../types/datatable.types';

interface DataTableRowActionsProps {
    row: Row<TData>;
    table: Table<TData>;
    defaultActionsConfig: DefaultRowActionsConfig<TData>;
    isDeletedMode: boolean;
    customActions?: RowAction<TData>[];
}

const props = defineProps<DataTableRowActionsProps>();


const activeCustomActions = computed(() => {
    if (props.isDeletedMode) {
        return (props.table.options.meta as any)?.deletedRowActions || [];
    }
    return (props.table.options.meta as any)?.rowActions || [];
});

type ActionKeys =
    | keyof NonNullable<DefaultRowActionsConfig<Identifiable>["normalMode"]>
    | keyof NonNullable<DefaultRowActionsConfig<Identifiable>["deletedMode"]>;

const getActionConfig = (
    actionName: ActionKeys
) => {
    const modeConfig = props.isDeletedMode
        ? props.defaultActionsConfig?.deletedMode
        : props.defaultActionsConfig?.normalMode;

    // Type assertion: modeConfig as Record<string, DefaultRowActionConfig | boolean>
    const config = (modeConfig as Record<string, DefaultRowActionConfig | boolean> | undefined)?.[actionName];
    if (typeof config === 'boolean')
        return { enabled: config, permission: undefined };
    return config || { enabled: true, permission: undefined };
};
const defaultActions = computed<RowAction<TData>[]>(() => {
    const item = props.row.original;
    const actionsList: RowAction<TData>[] = [];

    if (props.isDeletedMode) {
        const restoreConfig = getActionConfig('restore');
        if (restoreConfig.enabled && props.defaultActionsConfig?.restoreRoute) {
            actionsList.push({
                label: 'Restaurar',
                icon: RotateCcw,
                actionKind: 'http',
                httpMethod: 'POST',
                endpoint: props.defaultActionsConfig.restoreRoute(item),
                permission: restoreConfig.permission,
                confirm: true,
                confirmMessage: '¿Seguro que quieres restaurar este elemento?',
                uiBehavior: 'notifyRefresh',
                onSuccess: restoreConfig.onSuccess,
                onError: restoreConfig.onError,
            });
        }
        const forceDeleteConfig = getActionConfig('permanentlyDelete');
        if (forceDeleteConfig.enabled && props.defaultActionsConfig?.forceDeleteRoute) {
            actionsList.push({
                label: 'Eliminar Perm.',
                icon: ShieldAlert,
                actionKind: 'http',
                httpMethod: 'DELETE',
                endpoint: props.defaultActionsConfig.forceDeleteRoute(item),
                permission: forceDeleteConfig.permission,
                confirm: true,
                confirmMessage: '¡Esta acción es irreversible! ¿Seguro que quieres eliminar permanentemente?',
                uiBehavior: 'notifyRefresh',
                class: 'text-destructive focus:text-destructive',
                onSuccess: forceDeleteConfig.onSuccess,
                onError: forceDeleteConfig.onError,
            });
        }
    } else {
        const previewConfig = getActionConfig('preview');
        if (
            previewConfig.enabled &&
            props.defaultActionsConfig?.previewComponent &&
            props.defaultActionsConfig?.openPreviewSheet &&
            props.defaultActionsConfig?.previewSheetId
        ) {
            actionsList.push({
                label: 'Previsualizar',
                icon: Eye,
                actionKind: 'function',
                permission: previewConfig.permission,
                actionFn: (rowData) => {
                    if (props.defaultActionsConfig.previewSheetId && props.defaultActionsConfig.openPreviewSheet) {
                        // eslint-disable-next-line vue/no-mutating-props
                        props.defaultActionsConfig.previewSheetId.value = rowData.id;
                        // eslint-disable-next-line vue/no-mutating-props
                        props.defaultActionsConfig.openPreviewSheet.value = true;
                    }
                },
            });
        }

        const viewConfig = getActionConfig('view');
        if (viewConfig.enabled && props.defaultActionsConfig?.viewRoute) {
            actionsList.push({
                label: 'Ver',
                icon: ExternalLink,
                actionKind: 'route',
                routeName: props.defaultActionsConfig.viewRoute(item),
                routeParams: props.defaultActionsConfig.viewRouteParams ? props.defaultActionsConfig.viewRouteParams : undefined,
                permission: viewConfig.permission,
                order: viewConfig.order,
            });
        }
        const editConfig = getActionConfig('edit');
        if (editConfig.enabled && props.defaultActionsConfig?.editRoute) {
            actionsList.push({
                label: 'Editar',
                icon: Edit,
                actionKind: 'route',
                routeName: props.defaultActionsConfig.editRoute(item), // Nombre de la ruta
                routeParams: props.defaultActionsConfig.editRouteParams ? props.defaultActionsConfig.editRouteParams : undefined,
                permission: editConfig.permission,
                order: editConfig.order,
            });
        }
        const deleteConfig = getActionConfig('delete');
        if (deleteConfig.enabled && props.defaultActionsConfig?.deleteRoute) {
            actionsList.push({
                label: 'Borrar',
                icon: Trash2,
                actionKind: 'http',
                httpMethod: 'DELETE',
                endpoint: props.defaultActionsConfig.deleteRoute(item),
                permission: deleteConfig.permission,
                confirm: true,
                confirmMessage: '¿Seguro que quieres eliminar este elemento?',
                uiBehavior: 'notifyRefresh',
                class: 'text-destructive focus:text-destructive',
                order: deleteConfig.order,
                onSuccess: deleteConfig.onSuccess,
                onError: deleteConfig.onError,
            });
        }
    }

    return actionsList;
});

const allVisibleActions = computed(() => {
    const combined = [...defaultActions.value, ...activeCustomActions.value];

    return combined
        .filter((action) => {
            const isDisabled = action.disabled?.(props.row.original);
            const hasPerm = action.hasAccess
                ? action.hasAccess(props.row.original)
                : (!action.permission)


            if (isDisabled) return false;
            return hasPerm;
        })
        .sort((a, b) => (a.order ?? 99) - (b.order ?? 99));
});

const handleHttpAction = async (action: RowAction<TData>) => {
    const rowData = props.row.original;
    const endpointUrl = typeof action.endpoint === 'function' ? action.endpoint(rowData) : action.endpoint;

    if (!endpointUrl) {
        toast.error('Error: Endpoint no definido para la acción.');
        return;
    }

    if (action.confirm) {
        const isConfirmed = await useConfirmationDialog().openDialog({
            title: action.confirmMessage || 'Confirmar acción',
            message: '¿Estás seguro de que quieres continuar?',
        });

        if (!isConfirmed) {
            return;
        }
    }

    try {
        const method = action.httpMethod?.toLowerCase() || 'post';
        // @ts-expect-error: axios[method] index signature is not typed, but we know method is valid HTTP verb
        await axios[method](endpointUrl); // Usar axios para peticiones API

        if (action.onSuccess) {
            action.onSuccess();
        } else {
            toast.success(action.label + ' exitoso.');
        }
        if (action.uiBehavior === 'refresh' || action.uiBehavior === 'notifyRefresh') {
            (props.table.options.meta as any)?.refreshData?.();
        }
    } catch (error: any) {
        const errorMessage = error.response?.data?.message || error.message || 'Acción fallida';
        if (action.onError) {
            action.onError(error);
        } else {
            toast.error(`Error en ${action.label.toLowerCase()}: ${errorMessage}`);
        }
        console.error(`Error en acción ${action.label}:`, error);
    }
};

const executeAction = async (action: RowAction<TData>) => {
    const rowData = props.row.original;
    if (action.actionKind === 'http') {
        await handleHttpAction(action);
    } else if (action.actionKind === 'route' && action.routeName) {
        const routeParams = action.routeParams ? action.routeParams(rowData) : rowData.id;
        router.visit(route(action.routeName, routeParams as any)); // route() es de Ziggy/Inertia
    } else if (action.actionKind === 'event' && action.eventName) {
        (props.table.options.meta as any)?.customActionHandler?.(action.eventName, rowData);
    } else if (action.actionKind === 'function' && action.actionFn) {
        if (action.confirm) {
            const isConfirmed = await useConfirmationDialog().openDialog({
                title: action.confirmMessage || 'Confirmar acción',
                message: '¿Estás seguro de que quieres continuar?',
            });
            if (!isConfirmed) return;
        }
        await action.actionFn(rowData, props.table);
    }
};
</script>

<template>
    {{allVisibleActions.length > 0 ? '' : 'No hay acciones disponibles'}}
    <div v-if="allVisibleActions.length > 0" class="flex justify-end">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 p-0">
                    <MoreVertical class="h-4 w-4" />
                    <span class="sr-only">Abrir menú</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-[180px]">
                <template v-for="(action, index) in allVisibleActions" :key="action.label + index">
                    <DropdownMenuSeparator v-if="action.label === '-'" />
                    <DropdownMenuItem v-else @click.stop="executeAction(action)" :class="action.class" :disabled="action.disabled?.(row.original)">
                        <component v-if="action.icon && typeof action.icon !== 'string'" :is="action.icon" class="mr-2 h-4 w-4" />
                        {{ action.label }}
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
