<script setup lang="ts">
import { ComboboxAnchor, ComboboxInput, ComboboxRoot } from 'reka-ui';
import { computed, ref } from 'vue';
import {
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxItem,
    ComboboxList,
    ComboboxViewport,
} from '@/components/ui/combobox';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';
import type { SelectOption } from '@/types';

const props = defineProps<{
    id: string;
    name: string;
    options: SelectOption[];
    defaultValue?: string[];
}>();

const selected = ref<string[]>([...(props.defaultValue ?? [])]);
const isOpen = ref(false);
const searchTerm = ref('');

const labels = computed(
    () => new Map(props.options.map((option) => [option.value, option.label])),
);

// Same idea as the event search: case, Polish letters and "ł" do not matter.
const normalize = (text: string): string =>
    text
        .toLocaleLowerCase('pl')
        .replaceAll('ł', 'l')
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '');

const suggestions = computed(() => {
    const term = normalize(searchTerm.value.trim());

    return props.options.filter(
        (option) =>
            !selected.value.includes(option.value) &&
            normalize(option.label).includes(term),
    );
});

// Typed text that matches nothing must not become a tag.
const select = (values: unknown): void => {
    selected.value = (Array.isArray(values) ? values : [])
        .map(String)
        .filter((value) => labels.value.has(value));
    searchTerm.value = '';
};

const displayValue = (value: unknown): string =>
    labels.value.get(String(value)) ?? String(value);
</script>

<template>
    <div>
        <ComboboxRoot
            v-model:open="isOpen"
            :model-value="selected"
            multiple
            ignore-filter
            @update:model-value="select"
        >
            <ComboboxAnchor as-child>
                <TagsInput
                    :model-value="selected"
                    :display-value="displayValue"
                    class="w-full"
                    data-test="specializations-field"
                    @update:model-value="select"
                >
                    <TagsInputItem
                        v-for="value in selected"
                        :key="value"
                        :value="value"
                    >
                        <TagsInputItemText />
                        <TagsInputItemDelete />
                    </TagsInputItem>

                    <ComboboxInput v-model="searchTerm" as-child>
                        <TagsInputInput
                            :id="id"
                            class="h-7 min-w-32 flex-1 px-1"
                            :placeholder="
                                selected.length === 0
                                    ? $t('Type a specialization')
                                    : undefined
                            "
                            @keydown.enter.prevent
                            @focus="isOpen = true"
                        />
                    </ComboboxInput>
                </TagsInput>
            </ComboboxAnchor>

            <ComboboxList
                class="w-(--reka-combobox-trigger-width)"
                align="start"
            >
                <ComboboxViewport class="max-h-64 p-1">
                    <ComboboxEmpty class="py-4 text-center text-sm">
                        {{ $t('No matching specializations.') }}
                    </ComboboxEmpty>
                    <ComboboxGroup>
                        <ComboboxItem
                            v-for="option in suggestions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </ComboboxItem>
                    </ComboboxGroup>
                </ComboboxViewport>
            </ComboboxList>
        </ComboboxRoot>

        <input
            v-for="value in selected"
            :key="value"
            type="hidden"
            :name="`${name}[]`"
            :value="value"
        />
    </div>
</template>
