<script setup lang="ts">
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

interface DateDisplayCellProps {
  value: string; // ejemplo: '2025-07-20T00:00:00.000000Z' o '2025-07-11T13:10:53.000000Z'
}

const props = defineProps<DateDisplayCellProps>();

// Extrae la fecha local ignorando zona horaria
const getLocalDate = (isoDateTime: string): Date => {
  const datePart = isoDateTime.split('T')[0]; // '2025-07-20'
  const [year, month, day] = datePart.split('-').map(Number);
  return new Date(year, month - 1, day);
};

// Verifica si la hora es distinta de 00:00:00
const hasTimeInfo = (isoDateTime: string): boolean => {
  const timePart = isoDateTime.split('T')[1]; // '00:00:00.000000Z'
  if (!timePart) return false;

  // Asegura que sea algo distinto de "00:00:00"
  return !/^00:00:00/.test(timePart);
};
</script>

<template>
  <TooltipProvider>
    <Tooltip>
      <TooltipTrigger>
        <span>{{ format(getLocalDate(props.value), 'PPP', { locale: es }) }}</span>
      </TooltipTrigger>
      <TooltipContent>
        <span>
          {{
            hasTimeInfo(props.value)
              ? format(new Date(props.value), 'PPP ppp', { locale: es })
              : format(getLocalDate(props.value), 'PPP', { locale: es })
          }}
        </span>
      </TooltipContent>
    </Tooltip>
  </TooltipProvider>
</template>
