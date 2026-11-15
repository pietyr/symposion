<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    defaultValue?: string | null;
    error?: string;
}>();

// Common titles to pick from; any other title can still be typed in.
const suggestions = [
    'Physician (title)',
    'Dentist (title)',
    'Doctor of medical sciences (title)',
    'Habilitated doctor of medical sciences (title)',
    'Professor of medical sciences (title)',
    'Master (title)',
    'Master of nursing (title)',
    'Bachelor (title)',
];
</script>

<template>
    <div class="grid gap-2">
        <Label for="academic_title">{{
            $t('Academic title (optional)')
        }}</Label>
        <Input
            id="academic_title"
            name="academic_title"
            list="academic-title-suggestions"
            maxlength="50"
            autocomplete="honorific-prefix"
            :default-value="defaultValue ?? undefined"
        />
        <datalist id="academic-title-suggestions">
            <option
                v-for="suggestion in suggestions"
                :key="suggestion"
                :value="$t(suggestion)"
            />
        </datalist>
        <InputError :message="error" />
    </div>
</template>
