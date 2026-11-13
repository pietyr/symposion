<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePriceFormat } from '@/composables/usePriceFormat';
import type {
    PublicBenefitOptionResource,
    PublicBenefitResource,
} from '@/types';

const props = defineProps<{
    benefit: PublicBenefitResource;
    error?: string;
}>();

// A list of option ids for checkboxes, one id (or nothing) for a single choice.
const choice = defineModel<string | string[]>({ required: true });

const { formatPrice } = usePriceFormat();

// A select item cannot have an empty value, so "no choice" needs its own.
const noChoice = 'none';

const name = computed(() =>
    props.benefit.type === 'checkbox'
        ? `benefits[${props.benefit.id}][]`
        : `benefits[${props.benefit.id}]`,
);

const isAvailable = (option: PublicBenefitOptionResource): boolean =>
    !option.is_disabled && option.seats_left !== 0;

const note = (option: PublicBenefitOptionResource): string | null => {
    if (option.is_disabled) {
        return 'Unavailable';
    }

    return option.seats_left === 0 ? 'No seats left' : null;
};

const selected = computed({
    get: () => (choice.value === '' ? noChoice : String(choice.value)),
    set: (value: string) => {
        choice.value = value === noChoice ? '' : value;
    },
});
</script>

<template>
    <fieldset class="flex flex-col gap-3" :data-test="`benefit-${benefit.id}`">
        <legend class="mb-3 font-medium">{{ benefit.name }}</legend>

        <template v-if="benefit.type === 'select'">
            <Select v-model="selected">
                <SelectTrigger
                    class="w-full"
                    :aria-label="benefit.name"
                    :data-test="`benefit-${benefit.id}-select`"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-if="!benefit.is_required" :value="noChoice">
                        {{ $t('No choice') }}
                    </SelectItem>
                    <SelectItem
                        v-for="option in benefit.options"
                        :key="option.id"
                        :value="String(option.id)"
                        :disabled="!isAvailable(option)"
                    >
                        {{ option.name }} · {{ formatPrice(option.price) }}
                        <template v-if="note(option)">
                            · {{ $t(note(option) ?? '') }}
                        </template>
                    </SelectItem>
                </SelectContent>
            </Select>
            <input type="hidden" :name="name" :value="choice" />
        </template>

        <template v-else>
            <label
                v-if="benefit.type === 'radio' && !benefit.is_required"
                class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 has-checked:border-primary has-checked:ring-1 has-checked:ring-primary"
            >
                <input
                    v-model="choice"
                    type="radio"
                    :name="name"
                    value=""
                    class="size-4 accent-primary"
                />
                <span class="font-medium">{{ $t('No choice') }}</span>
            </label>

            <label
                v-for="option in benefit.options"
                :key="option.id"
                class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border p-4 has-checked:border-primary has-checked:ring-1 has-checked:ring-primary has-disabled:cursor-not-allowed has-disabled:opacity-50"
                :data-test="`benefit-option-${option.id}`"
            >
                <span class="flex items-center gap-3">
                    <input
                        v-model="choice"
                        :type="
                            benefit.type === 'checkbox' ? 'checkbox' : 'radio'
                        "
                        :name="name"
                        :value="String(option.id)"
                        :disabled="!isAvailable(option)"
                        class="size-4 accent-primary"
                    />
                    <span class="flex flex-col">
                        <span class="font-medium">{{ option.name }}</span>
                        <span
                            v-if="note(option)"
                            class="text-sm text-muted-foreground"
                        >
                            {{ $t(note(option) ?? '') }}
                        </span>
                    </span>
                </span>
                <span class="font-medium tabular-nums">{{
                    formatPrice(option.price)
                }}</span>
            </label>
        </template>

        <InputError :message="error" />
    </fieldset>
</template>
