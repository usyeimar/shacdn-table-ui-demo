// src/components/datatable/DataTableToolbar.vue
<script setup lang="ts" generic="TData extends Identifiable">
import DataTableFilters from '@/components/filters/DataTableFilters.vue'; // [cite: 738, 740]
import { Button } from '@/components/ui/button'; // [cite: 550, 565, 569, 596, 602, 624, 630, 636, 649, 652, 667, 672, 677, 680, 682, 737, 740, 798, 800, 812, 826, 877, 1097, 1101, 1102, 1137, 1142, 1287, 1292, 1307, 1317, 1325, 1337, 1348, 1459, 1501, 1518, 1626, 1628, 1717, 1722, 1738, 1740, 1749, 1751, 1753, 1756, 1758, 1760, 1762, 1765, 1800, 1806, 1819, 1827]
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { type Table } from '@tanstack/vue-table';
import { Download, SlidersHorizontalIcon, XIcon, Trash2, ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import type { ExportFormat, FilterConfig, Identifiable } from '../index';

interface DataTableToolbarProps {
    table: Table<TData>;
    searchPlaceholder?: string;
    customFilters?: FilterConfig[];
    appliedFilters: Record<string, string>;
    exportConfig?: {
        formats: ExportFormat[];
        endpoint: string;
        filename: string;
        onExport: (format: ExportFormat) => Promise<void>;
    };
    enableDeletedModeToggle?: boolean;
    deletedMode?: boolean;
}

const props = defineProps<DataTableToolbarProps>();

const localAppliedFilters = computed({
    get: () => props.appliedFilters,
    set: (value) => emit('update:appliedFilters', value)
});

const isAnyFilterActive = computed(() => {
    return Object.values(props.appliedFilters).some(val => val && val !== '');
});

const emit = defineEmits<{
    (e: 'update:appliedFilters', value: Record<string, string>): void;
    (e: 'clear-all-filters'): void;
    (e: 'export'): void;
    (e: 'toggle-deleted-mode'): void;
}>();



const clearAllFiltersAndSearch = () => {
    emit('clear-all-filters');
};

</script>

<template>
    <div class="flex flex-col space-y-4 py-4">
        <div class="flex items-center justify-between">
            <div class="flex flex-1 items-center space-x-2">
                <Button v-if="isAnyFilterActive" variant="ghost" size="sm" @click="clearAllFiltersAndSearch" class="h-9 px-2 lg:px-3">
                    Limpiar Filtros
                    <XIcon class="ml-2 h-4 w-4" />
                </Button>
                <slot name="toolbar-actions"></slot>
            </div>

            <div class="flex items-center gap-2">
            </div>
        </div>

        <div class="flex items-start justify-between flex-wrap gap-2">
            <div class="flex-1 min-w-0 w-full lg:w-auto">
                <DataTableFilters
                    v-if="props.customFilters && props.customFilters.length > 0"
                    v-model="localAppliedFilters"
                    :filters="props.customFilters"
                    @clear="() => emit('update:appliedFilters', {})"
                    :searchPlaceholder="props.searchPlaceholder"
                />
            </div>

            <div class="flex items-center gap-2 ml-auto self-start">
                <Button v-if="props.exportConfig" variant="outline" size="sm" class="h-9" @click="emit('export')">
                    <Download class="mr-2 h-4 w-4" />
                    Exportar
                </Button>

                <Button
                    v-if="props.enableDeletedModeToggle"
                    :variant="props.deletedMode ? 'secondary' : 'destructive'"
                    size="sm"
                    class="h-9"
                    @click="emit('toggle-deleted-mode')"
                >
                    <component :is="props.deletedMode ? ArrowLeft : Trash2" class="mr-2 h-4 w-4" />
                    {{ props.deletedMode ? 'Volver' : 'Ir a la papelera' }}
                </Button>
            </div>
        </div>
    </div>
</template>
