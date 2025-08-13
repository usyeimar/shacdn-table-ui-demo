<script setup lang="ts">
// Vue and composables
import { useTheme } from '@/composables/useTheme';

// UI Components and Layouts
import { Button } from './ui/button';

// Icons
import { MoonIcon, SunIcon } from 'lucide-vue-next';

const { isDark } = useTheme();
</script>

<template>
    <Button variant="ghost" size="icon" class="h-8 w-8 group cursor-pointer" @click="isDark = !isDark">
        <SunIcon v-if="isDark" class="h-4 w-4 transition-colors group-hover:text-accent-foreground" />
        <MoonIcon v-else class="h-4 w-4 transition-colors group-hover:text-accent-foreground" />
    </Button>
</template>
