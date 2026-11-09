<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import {
    ComboboxAnchor,
    ComboboxInput,
    ComboboxRoot,
    ComboboxTrigger,
} from 'reka-ui';
import { onMounted, ref, watch } from 'vue';
import SearchParticipantsController from '@/actions/App/Http/Controllers/Admin/SearchParticipantsController';
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

defineProps<{
    id: string;
    name: string;
}>();

const http = useHttp();

const selected = ref<string>('');
const selectedOption = ref<ParticipantOption | null>(null);
const searchTerm = ref('');
const suggestions = ref<ParticipantOption[]>([]);
const isSearching = ref(false);

// A slower answer to an older search must not replace a newer one.
let latestSearch = 0;

const search = async (term: string): Promise<void> => {
    const current = ++latestSearch;
    isSearching.value = true;

    try {
        const found = (await http.submit(
            SearchParticipantsController({ query: { search: term } }),
        )) as ParticipantOption[];

        if (current === latestSearch) {
            suggestions.value = found;
        }
    } catch {
        if (current === latestSearch) {
            suggestions.value = [];
        }
    } finally {
        if (current === latestSearch) {
            isSearching.value = false;
        }
    }
};

// Waits for a pause in typing, so a name sends one request instead of one per letter.
watch(
    searchTerm,
    useDebounceFn((term: string) => search(term), 250),
);

onMounted(() => search(''));

// The picked participant may drop out of the suggestions after the next search.
watch(selected, (value) => {
    selectedOption.value =
        suggestions.value.find((option) => option.value === value) ?? null;
});

const displayValue = (): string => selectedOption.value?.label ?? '';
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
                        {{
                            isSearching
                                ? $t('Searching…')
                                : $t('No matching participants.')
                        }}
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
