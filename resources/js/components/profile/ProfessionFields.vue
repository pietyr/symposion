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
import SpecializationsField from '@/components/profile/SpecializationsField.vue';
import type {
    ProfessionalProfile,
    ProfessionOption,
    SelectOption,
} from '@/types';

const props = defineProps<{
    professions: ProfessionOption[];
    specializations: SelectOption[];
    errors: Partial<Record<string, string>>;
    profile?: ProfessionalProfile;
    isProfessionRequired?: boolean;
}>();

const profession = ref(props.profile?.profession ?? '');
const selected = computed(() =>
    props.professions.find((option) => option.value === profession.value),
);

const detailLabels = {
    university: { label: 'University', autocomplete: 'organization' },
    student_number: { label: 'Student ID number', autocomplete: 'off' },
    company: { label: 'Company', autocomplete: 'organization' },
    profession_name: {
        label: 'Your profession',
        autocomplete: 'organization-title',
    },
} as const;

type DetailField = keyof typeof detailLabels;

const detailFields = computed(() =>
    (selected.value?.details ?? [])
        .filter((name): name is DetailField => name in detailLabels)
        .map((name) => ({ name, ...detailLabels[name] })),
);
</script>

<template>
    <div class="grid gap-6">
        <div class="grid gap-2">
            <Label for="profession">{{ $t('Profession') }}</Label>
            <Select
                v-model="profession"
                name="profession"
                :required="isProfessionRequired ?? true"
            >
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
                :default-value="profile?.license_number ?? undefined"
                autocomplete="off"
                data-test="license-number"
            />
            <InputError :message="errors.license_number" />
        </div>

        <div v-if="selected?.specializations" class="grid gap-2">
            <Label for="specializations">{{
                $t('Specializations (optional)')
            }}</Label>
            <SpecializationsField
                id="specializations"
                name="specializations"
                :options="specializations"
                :default-value="profile?.specializations"
            />
            <InputError :message="errors.specializations" />
        </div>

        <div v-for="field in detailFields" :key="field.name" class="grid gap-2">
            <Label :for="field.name">{{ $t(field.label) }}</Label>
            <Input
                :id="field.name"
                :name="field.name"
                required
                :default-value="profile?.[field.name] ?? undefined"
                :autocomplete="field.autocomplete"
            />
            <InputError :message="errors[field.name]" />
        </div>
    </div>
</template>
