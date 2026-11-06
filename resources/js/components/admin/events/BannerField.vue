<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

defineProps<{
    name: string;
    label: string;
    url?: string | null;
    error?: string;
}>();
</script>

<template>
    <div class="grid gap-2">
        <Label :for="name">{{ label }}</Label>
        <img
            v-if="url"
            :src="url"
            :alt="label"
            class="max-h-48 w-fit max-w-full rounded-md border"
        />
        <input
            :id="name"
            type="file"
            :name="name"
            accept="image/jpeg,image/png,image/webp"
            class="h-9 w-full min-w-0 cursor-pointer rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs file:mr-3 file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground"
        />
        <div v-if="url" class="flex items-center gap-3">
            <Checkbox
                :id="`remove_${name}`"
                :name="`remove_${name}`"
                value="1"
            />
            <Label :for="`remove_${name}`">{{ $t('Remove banner') }}</Label>
        </div>
        <InputError :message="error" />
    </div>
</template>
