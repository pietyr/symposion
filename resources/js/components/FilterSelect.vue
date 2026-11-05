<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { SelectOption } from '@/types';

defineProps<{
    modelValue: string;
    placeholder: string;
    items: SelectOption[];
    testId?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// A select item cannot have an empty value, so "no filter" needs its own.
const noFilter = 'all';

const update = (value: unknown): void => {
    emit('update:modelValue', value === noFilter ? '' : String(value));
};
</script>

<template>
    <Select :model-value="modelValue || noFilter" @update:model-value="update">
        <SelectTrigger
            class="w-full"
            :aria-label="placeholder"
            :data-test="testId"
        >
            <SelectValue />
        </SelectTrigger>
        <SelectContent>
            <SelectItem :value="noFilter">{{ placeholder }}</SelectItem>
            <SelectItem
                v-for="item in items"
                :key="item.value"
                :value="item.value"
            >
                {{ item.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
