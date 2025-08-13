import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { Updater } from '@tanstack/vue-table';
import { Ref } from 'vue';
import type { BadgeVariants } from '@/components/ui/badge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function valueUpdater<T extends Updater<any>>(updaterOrValue: T, ref: Ref) {
    ref.value
        = typeof updaterOrValue === 'function'
        ? updaterOrValue(ref.value)
        : updaterOrValue
}

const statusVariantMap: Record<string, BadgeVariants['variant']> = {
    'activo': 'success',
    'inactivo': 'destructive',
    'aprobado': 'success',
    'finalizado': 'destructive',
    'rechazado': 'destructive',
    'pendiente de aprobacion': 'warning',
    'en revision': 'secondary',
    'pendiente': 'warning',
    'en_proceso': 'info',
    'en novedad': 'secondary',
    'en_novedad': 'secondary',
    'completada': 'success',
    'pendiente_de_aprobacion': 'warning',
    'en_revision': 'secondary',
    'gestionar': 'warning',
    'info': 'info',
};

export { statusVariantMap };

export function getStatusVariant(status: string): BadgeVariants['variant'] {
    return statusVariantMap[status.toLowerCase()];
}
