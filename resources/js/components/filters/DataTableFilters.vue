<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';

import type { FilterConfig } from '@/types';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { Calendar as CalendarIcon, Filter, Search, X } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { computed, ref, watch, watchEffect } from 'vue';

// Imports para @internationalized/date
import { getLocalTimeZone, parseDate, type DateValue } from '@internationalized/date';

interface Props {
    modelValue: Record<string, any>;
    filters: FilterConfig[];
    showFilters?: boolean;
    searchPlaceholder?: string;
    globalSearchKey?: string;
}

const props = withDefaults(defineProps<Props>(), {
    showFilters: false,
    searchPlaceholder: 'Buscar...',
});

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, any>];
    clear: [];
}>();

const filterState = ref<Record<string, any>>({ ...props.modelValue });

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (newExternalValue) => {
        if (JSON.stringify(newExternalValue) !== JSON.stringify(filterState.value)) {
            filterState.value = { ...newExternalValue };
        }
    },
    { deep: true },
);

// Watch for internal changes to filterState
watch(
    filterState,
    (newInternalValue) => {
        if (JSON.stringify(newInternalValue) !== JSON.stringify(props.modelValue)) {
            emit('update:modelValue', { ...newInternalValue });
        }
    },
    { deep: true },
);

const activeFilters = computed(() => {
    return Object.entries(filterState.value)
        .filter(([, value]) => value !== '' && value !== undefined && value !== null)
        .map(([key]) => {
            const filter = props.filters.find((f) => f.key === key);
            let displayValue = filterState.value[key];

            if (filter?.type === 'date' && displayValue) {
                try {
                    displayValue = format(parseISO(String(displayValue)), 'PPP', { locale: es });
                } catch (error) {
                    console.error('Error formatting date for display:', error, 'Value:', displayValue);
                    displayValue = String(filterState.value[key]);
                }
            } else if (filter?.type === 'select' && filter.options) {
                const selectedOption = filter.options.find((opt) => opt.value === displayValue);
                displayValue = selectedOption ? selectedOption.label : String(displayValue);
            } else if (filter?.type === 'multiselect' && filter.options && Array.isArray(displayValue)) {
                // Mapear cada valor al label correspondiente
                const labels = displayValue
                    .map((val: string) => {
                        const opt = filter.options!.find((o) => o.value === val);
                        return opt ? opt.label : val;
                    })
                    .filter(Boolean);
                displayValue = labels.join(', ');
            } else if (filter?.type === 'date-range' && displayValue && typeof displayValue === 'object' && displayValue !== null) {
                try {
                    // Handle the stored format { from: string, to: string }
                    const rangeValue = displayValue as { from: string; to: string };
                    if (rangeValue.from && rangeValue.to) {
                        const startDate = format(parseISO(rangeValue.from), 'PPP', { locale: es });
                        const endDate = format(parseISO(rangeValue.to), 'PPP', { locale: es });
                        displayValue = `${startDate} - ${endDate}`;
                    } else {
                        displayValue = '';
                    }
                } catch (error) {
                    console.error('Error formatting date range for display:', error, 'Value:', displayValue);
                    displayValue = '';
                }
            }

            return {
                key,
                label: filter?.label || key,
                value: String(displayValue),
            };
        })
        .filter((item) => item.value !== '' && item.value !== 'null' && item.value !== 'undefined');
});

const globalFilterKey = computed(() => {
    if (props.globalSearchKey && props.filters.some((f) => f.key === props.globalSearchKey)) {
        return props.globalSearchKey;
    }
    const firstTextFilter = props.filters.find((f) => f.type === 'text');
    return firstTextFilter ? firstTextFilter.key : '_globalSearchFallback'; // Fallback key
});

const basicFilters = computed(() => props.filters.filter((f) => (f.group === 'basic' || !f.group) && f.key !== globalFilterKey.value));

const advancedFilters = computed(() => props.filters.filter((f) => f.group === 'advanced' && f.key !== globalFilterKey.value));

const clearFilters = () => {
    const clearedState: Record<string, any> = {};
    Object.keys(filterState.value).forEach((key) => {
        clearedState[key] = '';
    });
    filterState.value = clearedState; // This will trigger the watcher to emit update
    emit('clear'); // Emit clear separately if needed for other actions
};

const stringToDateValue = (dateString: string | undefined): DateValue | undefined => {
    if (!dateString || dateString.trim() === '') return undefined;
    try {
        return parseDate(dateString);
    } catch (error) {
        console.error('Error parsing date string for Calendar:', dateString, error);
        return undefined;
    }
};

const handleDateUpdate = (key: string, dateValue: DateValue | undefined) => {
    if (dateValue) {
        const jsDate = toDate(dateValue, getLocalTimeZone());
        filterState.value[key] = format(jsDate, 'yyyy-MM-dd');
    } else {
        filterState.value[key] = '';
    }
};

// Helper function to convert string to DateRange for RangeCalendar
const getDateRangeValue = (key: string) => {
    const value = filterState.value[key];
    if (!value || typeof value !== 'object' || value === null) return null;

    try {
        // Assuming the stored format has 'from' and 'to' properties with ISO date strings
        const rangeValue = value as { from: string; to: string };
        if (rangeValue.from && rangeValue.to) {
            return {
                start: parseDate(rangeValue.from),
                end: parseDate(rangeValue.to),
            };
        }
    } catch (error) {
        console.error('Error parsing date range:', error);
    }
    return null;
};

// Helper function to handle DateRange updates
const handleDateRangeUpdate = (key: string, dateRange: any) => {
    if (dateRange && dateRange.start && dateRange.end) {
        const startDate = toDate(dateRange.start, getLocalTimeZone());
        const endDate = toDate(dateRange.end, getLocalTimeZone());
        filterState.value[key] = {
            from: format(startDate, 'yyyy-MM-dd'),
            to: format(endDate, 'yyyy-MM-dd'),
        };
    } else {
        filterState.value[key] = '';
    }
};

const updateGlobalFilter = (value: string | number | null | undefined) => {
    filterState.value[globalFilterKey.value] = String(value ?? '');
};

const clearFilter = (key: string) => {
    filterState.value[key] = '';
};

// Helper function to format date range display
const formatDateRangeDisplay = (filterKey: string, filter: FilterConfig) => {
    const value = filterState.value[filterKey];
    if (value && typeof value === 'object' && value !== null && 'from' in value && 'to' in value) {
        try {
            // Type assertion for date range with from/to properties
            const rangeValue = value as { from: string; to: string };
            const fromDate = format(parseISO(String(rangeValue.from)), 'PPP', { locale: es });
            const toDate = format(parseISO(String(rangeValue.to)), 'PPP', { locale: es });
            return `${fromDate} - ${toDate}`;
        } catch {
            return filter.placeholder || `Seleccionar ${filter.label.toLowerCase()}`;
        }
    }
    return filter.placeholder || `Seleccionar ${filter.label.toLowerCase()}`;
};

// Helper function to format single date display
const formatDateDisplay = (filterKey: string, filter: FilterConfig) => {
    const value = filterState.value[filterKey];
    if (value) {
        try {
            return format(parseISO(String(value)), 'PPP', { locale: es });
        } catch {
            return filter.placeholder || `Seleccionar ${filter.label.toLowerCase()}`;
        }
    }
    return filter.placeholder || `Seleccionar ${filter.label.toLowerCase()}`;
};

// Ensure globalFilterKey is always initialized in filterState
watchEffect(() => {
    const key = globalFilterKey.value;
    if (key && !Object.prototype.hasOwnProperty.call(filterState.value, key)) {
        // Initialize from modelValue if present for this key, otherwise empty string
        filterState.value[key] = props.modelValue[key] || '';
    }
});
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <Search class="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                <Input
                    :model-value="filterState[globalFilterKey] || ''"
                    :placeholder="searchPlaceholder"
                    class="pl-8"
                    @update:model-value="updateGlobalFilter"
                />
            </div>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="sm" class="h-9 gap-1">
                        <Filter class="h-4 w-4" />
                        Filtros
                        <Badge v-if="activeFilters.length" variant="secondary" class="ml-1 rounded-sm px-1 font-normal">
                            {{ activeFilters.length }}
                        </Badge>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[300px]">
                    <div class="flex items-center justify-between p-2">
                        <h4 class="text-sm font-medium">Filtros</h4>
                        <Button v-if="activeFilters.length" variant="ghost" size="sm" class="h-8 gap-1" @click="clearFilters">
                            <X class="h-4 w-4" />
                            Limpiar Filtros
                        </Button>
                    </div>
                    <Separator />
                    <Accordion type="single" collapsible class="w-full">
                        <AccordionItem v-if="basicFilters.length" value="basic">
                            <AccordionTrigger class="px-2"> Filtros Básicos </AccordionTrigger>
                            <AccordionContent>
                                <div class="space-y-4 p-2">
                                    <div v-for="filter in basicFilters" :key="filter.key" class="flex flex-col gap-1 space-y-2">
                                        <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                            {{ filter.label }}
                                        </label>
                                        <template v-if="filter.type === 'select'">
                                            <Select v-model="filterState[filter.key]">
                                                <SelectTrigger class="w-full">
                                                    <SelectValue :placeholder="filter.placeholder || `Seleccionar ${filter.label.toLowerCase()}`" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="option in filter.options" :key="option.value" :value="option.value">
                                                        {{ option.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </template>
                                        <template v-else-if="filter.type === 'multiselect'">
                                            <Select v-model="filterState[filter.key]" multiple class="w-full">
                                                <SelectTrigger class="w-full">
                                                    <SelectValue :placeholder="filter.placeholder || 'Selecciona uno o varios'" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="option in filter.options" :key="option.value" :value="option.value">
                                                        {{ option.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </template>
                                        <template v-else-if="filter.type === 'date-range'">
                                            <Popover>
                                                <PopoverTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        :class="[
                                                            'w-full justify-start text-left font-normal',
                                                            !filterState[filter.key] && 'text-muted-foreground',
                                                        ]"
                                                    >
                                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                                        {{ formatDateRangeDisplay(filter.key, filter) }}
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-auto p-0">
                                                    <RangeCalendar
                                                        :model-value="getDateRangeValue(filter.key)"
                                                        locale="es"
                                                        :number-of-months="2"
                                                        class="rounded-md border"
                                                        @update:model-value="(dateRange: any) => handleDateRangeUpdate(filter.key, dateRange)"
                                                    />
                                                </PopoverContent>
                                            </Popover>
                                        </template>
                                        <template v-else-if="filter.type === 'date'">
                                            <Popover>
                                                <PopoverTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        :class="[
                                                            'w-full justify-start text-left font-normal',
                                                            !filterState[filter.key] && 'text-muted-foreground',
                                                        ]"
                                                    >
                                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                                        {{ formatDateDisplay(filter.key, filter) }}
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-auto p-0">
                                                    <Calendar
                                                        :model-value="stringToDateValue(filterState[filter.key])"
                                                        :weekday-format="'short'"
                                                        class="rounded-md border"
                                                        locale="es"
                                                        @update:model-value="
                                                            (dateValue: DateValue | undefined) => handleDateUpdate(filter.key, dateValue)
                                                        "
                                                    />
                                                </PopoverContent>
                                            </Popover>
                                        </template>
                                        <Input
                                            v-else
                                            v-model="filterState[filter.key]"
                                            :placeholder="filter.placeholder || `Buscar por ${filter.label.toLowerCase()}`"
                                            :type="filter.type === 'number' ? 'number' : 'text'"
                                        />
                                    </div>
                                </div>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem v-if="advancedFilters.length" value="advanced">
                            <AccordionTrigger class="px-2"> Filtros Avanzados </AccordionTrigger>
                            <AccordionContent>
                                <div class="space-y-4 p-2">
                                    <div v-for="filter in advancedFilters" :key="filter.key" class="flex flex-col gap-1 space-y-2">
                                        <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                            {{ filter.label }}
                                        </label>
                                        <template v-if="filter.type === 'select'">
                                            <Select v-model="filterState[filter.key]">
                                                <SelectTrigger class="w-full">
                                                    <SelectValue :placeholder="filter.placeholder || `Seleccionar ${filter.label.toLowerCase()}`" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="option in filter.options" :key="option.value" :value="option.value">
                                                        {{ option.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </template>
                                        <template v-else-if="filter.type === 'multiselect'">
                                            <Select v-model="filterState[filter.key]" multiple class="w-full">
                                                <SelectTrigger class="w-full">
                                                    <SelectValue :placeholder="filter.placeholder || 'Selecciona uno o varios'" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="option in filter.options" :key="option.value" :value="option.value">
                                                        {{ option.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </template>
                                        <template v-else-if="filter.type === 'date-range'">
                                            <Popover>
                                                <PopoverTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        :class="[
                                                            'w-full justify-start text-left font-normal',
                                                            !filterState[filter.key] && 'text-muted-foreground',
                                                        ]"
                                                    >
                                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                                        {{ formatDateRangeDisplay(filter.key, filter) }}
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-auto p-0">
                                                    <RangeCalendar
                                                        :model-value="getDateRangeValue(filter.key)"
                                                        locale="es"
                                                        :number-of-months="2"
                                                        class="rounded-md border"
                                                        @update:model-value="(dateRange: any) => handleDateRangeUpdate(filter.key, dateRange)"
                                                    />
                                                </PopoverContent>
                                            </Popover>
                                        </template>
                                        <template v-else-if="filter.type === 'date'">
                                            <Popover>
                                                <PopoverTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        :class="[
                                                            'w-full justify-start text-left font-normal',
                                                            !filterState[filter.key] && 'text-muted-foreground',
                                                        ]"
                                                    >
                                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                                        {{ formatDateDisplay(filter.key, filter) }}
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-auto p-0">
                                                    <Calendar
                                                        :model-value="stringToDateValue(filterState[filter.key])"
                                                        :weekday-format="'short'"
                                                        class="rounded-md border"
                                                        locale="es"
                                                        @update:model-value="
                                                            (dateValue: DateValue | undefined) => handleDateUpdate(filter.key, dateValue)
                                                        "
                                                    />
                                                </PopoverContent>
                                            </Popover>
                                        </template>
                                        <Input
                                            v-else
                                            v-model="filterState[filter.key]"
                                            :placeholder="filter.placeholder || `Buscar por ${filter.label.toLowerCase()}`"
                                            :type="filter.type === 'number' ? 'number' : 'text'"
                                        />
                                    </div>
                                </div>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <div v-if="activeFilters.length" class="flex flex-wrap gap-2">
            <Badge v-for="filter in activeFilters" :key="filter.key" variant="secondary" class="gap-1 px-2 py-1">
                {{ filter.label }}: {{ filter.value }}
                <Button variant="ghost" size="sm" class="h-4 w-4 p-0 hover:bg-transparent" @click="() => clearFilter(filter.key)">
                    <X class="h-3 w-3" />
                </Button>
            </Badge>
        </div>
    </div>
</template>
