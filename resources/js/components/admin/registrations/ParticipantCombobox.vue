<script setup lang="ts">
import { ChevronsUpDown } from '@lucide/vue';
import {
    ComboboxAnchor,
    ComboboxInput,
    ComboboxRoot,
    ComboboxTrigger,
} from 'reka-ui';
import { computed, ref } from 'vue';
import {
    ComboboxEmpty,
    ComboboxItem,
    ComboboxList,
    ComboboxViewport,
} from '@/components/ui/combobox';

type ParticipantOption = {
    value: string;
    label: string;
    description: string;
};

const props = defineProps<{
    id: string;
    name: string;
    options: ParticipantOption[];
}>();

const selected = ref<string>('');
const searchTerm = ref('');

const labels = computed(
    () => new Map(props.options.map((option) => [option.value, option.label])),
);

const normalize = (text: string): string =>
    text
        .toLocaleLowerCase('pl')
        .replaceAll('ł', 'l')
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '');

// Every typed word has to appear in the name or the email address.
const suggestions = computed(() => {
    const words = normalize(searchTerm.value).split(/\s+/).filter(Boolean);

    return props.options.filter((option) => {
        const text = normalize(`${option.label} ${option.description}`);

        return words.every((word) => text.includes(word));
    });
});

const displayValue = (value: unknown): string =>
    labels.value.get(String(value)) ?? '';
</script>

<template>
    <div>
        <ComboboxRoot
            v-model="selected"
            ignore-filter
            open-on-click
            reset-search-term-on-blur
        >
            <ComboboxAnchor
                class="flex h-9 w-full items-center rounded-md border border-input bg-transparent px-3 text-sm shadow-xs focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50"
            >
                <ComboboxInput
                    :id="id"
                    v-model="searchTerm"
                    :display-value="displayValue"
                    :placeholder="$t('Select a participant')"
                    class="h-full w-full bg-transparent outline-none placeholder:text-muted-foreground"
                    data-test="participant-input"
                />
                <ComboboxTrigger :aria-label="$t('Select a participant')">
                    <ChevronsUpDown class="size-4 opacity-50" />
                </ComboboxTrigger>
            </ComboboxAnchor>

            <ComboboxList
                class="w-(--reka-combobox-trigger-width)"
                align="start"
            >
                <ComboboxViewport class="max-h-64 p-1">
                    <ComboboxEmpty class="py-4 text-center text-sm">
                        {{ $t('No matching participants.') }}
                    </ComboboxEmpty>
                    <ComboboxItem
                        v-for="option in suggestions"
                        :key="option.value"
                        :value="option.value"
                        class="flex-col items-start gap-0"
                    >
                        <span>{{ option.label }}</span>
                        <span class="text-xs text-muted-foreground">{{
                            option.description
                        }}</span>
                    </ComboboxItem>
                </ComboboxViewport>
            </ComboboxList>
        </ComboboxRoot>

        <input type="hidden" :name="name" :value="selected" />
    </div>
</template>
