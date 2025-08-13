<script setup lang="ts">
import { ExportFormat } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FileText } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    formats: ExportFormat[];
    onExport: (format: ExportFormat) => Promise<void>;
    title?: string;
    subtitle?: string;
    description?: string;
}>();

const emit = defineEmits<{
    (e: 'update:isOpen', value: boolean): void;
}>();

const selectedFormat = ref<ExportFormat | null>(null);

const handleExport = async () => {
    if (!selectedFormat.value) return;
    try {
        await props.onExport(selectedFormat.value);
        emit('update:isOpen', false);
        selectedFormat.value = null;
    } catch (error) {
        console.error('Error exporting:', error);
    }
};

const formatLabels: Record<ExportFormat, string> = {
    csv: 'CSV',
    xlsx: 'Excel',
    pdf: 'PDF',
    json: 'JSON',
};

const formatDescriptions: Record<ExportFormat, string> = {
    csv: 'Archivo separado por comas',
    xlsx: 'Hoja de cálculo Excel',
    pdf: 'Documento PDF',
    json: 'Formato JSON',
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="(val) => emit('update:isOpen', val)">
        <DialogContent class="sm:max-w-[480px]">
            <DialogHeader class="items-center text-center">
                <DialogTitle class="text-2xl font-bold">
                    {{ props.title || 'Exportar datos' }}
                </DialogTitle>
                <p class="text-muted-foreground mt-1">
                    {{ props.subtitle || 'Exporta tus datos en varios formatos' }}
                </p>
            </DialogHeader>
            <div class="flex flex-col items-center justify-center py-4">
                <div class="bg-primary/10 mb-4 rounded-full p-4">
                    <FileText class="text-primary h-14 w-14" />
                </div>
                <p class="text-muted-foreground mb-4 max-w-md text-center text-sm">
                    {{
                        props.description ||
                        'Selecciona el formato en el que deseas exportar los datos. El sistema preparará el archivo para su descarga. Si has seleccionado datos, solo se exportarán esos; de lo contrario, se exportará toda la lista.'
                    }}
                </p>
                <div class="mb-4 w-full max-w-xs">
                    <label class="mb-1 block text-sm font-medium">Formato de exportación</label>
                    <Select v-model="selectedFormat">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Selecciona un formato" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="format in formats" :key="format" :value="format" class="flex items-center gap-2">
                                <span class="font-medium">{{ formatLabels[format] }}</span>
                                <span class="text-muted-foreground ml-2 text-xs">{{ formatDescriptions[format] }}</span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <DialogFooter class="flex flex-row justify-end gap-2 pt-2">
                <Button variant="outline" @click="emit('update:isOpen', false)"> Cerrar</Button>
                <Button :disabled="!selectedFormat" @click="handleExport"> Exportar</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
