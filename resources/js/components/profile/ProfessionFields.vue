<script setup lang="ts">
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { SelectOption } from '@/types';

const props = defineProps<{
    professions: SelectOption[];
    errors: Partial<Record<string, string>>;
    defaultProfession?: string | null;
}>();

const profession = ref(props.defaultProfession ?? '');
</script>

<template>
    <div class="grid gap-6">
        <div class="grid gap-2">
            <Label for="profession">{{ $t('Profession') }}</Label>
            <Select v-model="profession" name="profession" required>
                <SelectTrigger
                    id="profession"
                    class="w-full"
                    data-test="profession-select"
                >
                    <SelectValue :placeholder="$t('Select your profession')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in professions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.profession" />
        </div>
    </div>
</template>
