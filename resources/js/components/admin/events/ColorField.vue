<script setup lang="ts">
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    name: string;
    label: string;
    defaultValue?: string | null;
    error?: string;
}>();

const color = ref(props.defaultValue ?? '');

// A color picker cannot be empty, so without a valid color it shows white instead of its own black.
const pickerColor = computed(() =>
    /^#[0-9a-f]{6}$/i.test(color.value) ? color.value : '#ffffff',
);
</script>

<template>
    <div class="grid gap-2">
        <Label :for="name">{{ label }}</Label>
        <div class="flex gap-2">
            <input
                type="color"
                class="h-9 w-12 shrink-0 cursor-pointer rounded-md border border-input bg-transparent p-1"
                :value="pickerColor"
                :aria-label="label"
                @input="color = ($event.target as HTMLInputElement).value"
            />
            <Input
                :id="name"
                v-model="color"
                :name="name"
                placeholder="#RRGGBB"
            />
        </div>
        <InputError :message="error" />
    </div>
</template>
