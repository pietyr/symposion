<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { RegistrationStatus } from '@/types';

defineProps<{
    status: RegistrationStatus;
    label: string;
}>();

const classes: Record<RegistrationStatus, string> = {
    pending:
        'border-amber-600 text-amber-700 dark:border-amber-500 dark:text-amber-400',
    paid: 'border-transparent bg-emerald-700 text-white',
    cancelled: 'border-transparent bg-muted text-muted-foreground line-through',
};
</script>

<template>
    <Badge variant="outline" :class="classes[status]">{{ label }}</Badge>
</template>
