<script setup lang="ts">
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { ProfessionOption } from '@/types';

const props = defineProps<{
    professions: ProfessionOption[];
    errors: Partial<Record<string, string>>;
    defaultProfession?: string | null;
    defaultLicenseNumber?: string | null;
}>();

const profession = ref(props.defaultProfession ?? '');
const selected = computed(() =>
    props.professions.find((option) => option.value === profession.value),
);
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

        <div v-if="selected?.license_number" class="grid gap-2">
            <Label for="license_number">{{
                selected.license_number === 'optional'
                    ? $t('License number (PWZ, optional)')
                    : $t('License number (PWZ)')
            }}</Label>
            <Input
                id="license_number"
                name="license_number"
                :required="selected.license_number === 'required'"
                :default-value="defaultLicenseNumber ?? undefined"
                autocomplete="off"
                data-test="license-number"
            />
            <InputError :message="errors.license_number" />
        </div>
    </div>
</template>
