<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { useInitials } from '@/composables/useInitials';
import { Button } from '@/components/ui/button';
import { ChevronRight } from 'lucide-vue-next';
import { statusVariantMap, getStatusVariant } from '@/lib/utils';
import * as LucideIcons from 'lucide-vue-next';
import { computed } from 'vue';

export interface BadgeInfo {
    text: string;
    variant?: keyof typeof statusVariantMap | BadgeVariants['variant'];
    class?: string;
    onClick?: () => void;
    icon?: any;
}

interface ButtonAction {
    label?: string;
    onClick: () => void;
    icon?: any;
}

const props = withDefaults(defineProps<{
    name: string;
    imageUrl?: string;
    badges?: BadgeInfo[];
    subtext?: string;
    buttonAction?: ButtonAction;
    showAvatar?: boolean;
    icon?: string;
}>(), {
    imageUrl: '',
    badges: () => [] as BadgeInfo[],
    subtext: '',
    buttonAction: undefined,
    showAvatar: true,
    icon: undefined,
});

const { getInitials: calculateInitials } = useInitials();

const iconComponent = computed(() => {
    if (!props.icon) return null;
    const iconName = props.icon
        .split('-')
        .map((str: string) => str.charAt(0).toUpperCase() + str.slice(1))
        .join('');
    return (LucideIcons as any)[iconName] || null;
});

function getValidVariant(variant: string | undefined): BadgeVariants['variant'] {
  const mapped = variant ? getStatusVariant(variant) : undefined;
  return mapped || (variant as BadgeVariants['variant']) || 'outline';
}
</script>

<template>
    <div class="flex items-center gap-3">
        <component
            v-if="iconComponent"
            :is="iconComponent"
            class="h-6 w-6 text-primary"
        />
        <Avatar v-if="showAvatar" class="h-10 w-10 rounded-md">
            <AvatarImage v-if="imageUrl" :src="imageUrl" :alt="name" />
            <AvatarFallback class="rounded-md bg-muted text-muted-foreground">
                {{ calculateInitials(name) }}
            </AvatarFallback>
        </Avatar>
        <div class="flex flex-1 flex-col">
            <div class="font-medium text-foreground">{{ name }}</div>
            <div v-if="subtext" class="text-xs text-muted-foreground whitespace-pre-line">{{ subtext }}</div>
            <div v-if="badges && badges.length > 0" class="mt-1 flex flex-wrap gap-1.5">
                <Badge
                    v-for="(badge, index) in badges"
                    :key="index"
                    :variant="getValidVariant(badge.variant ?? undefined)"
                    :class="[badge.class, badge.onClick ? 'cursor-pointer hover:opacity-80' : '']"
                    @click="badge.onClick"
                >
                    <component v-if="badge.icon" :is="badge.icon" />
                    {{ badge.text }}
                </Badge>
            </div>
        </div>
        <Button
            v-if="buttonAction"
            variant="ghost"
            size="icon"
            class="h-8 w-8"
            @click="buttonAction.onClick"
        >
            <ChevronRight class="h-4 w-4" />
        </Button>
    </div>
</template>
