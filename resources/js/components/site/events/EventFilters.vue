<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { EventFilterOptions, EventFilters } from '@/types';

const props = defineProps<{
    url: string;
    filters: EventFilters;
    options: EventFilterOptions;
}>();

// A select item cannot have an empty value, so "no filter" needs its own.
const noFilter = 'all';

const search = ref(props.filters.search);

const hasFilters = computed(() =>
    Object.values(props.filters).some((value) => value !== ''),
);

const apply = (changes: Partial<EventFilters>): void => {
    const filters = { ...props.filters, search: search.value, ...changes };

    router.get(
        props.url,
        {
            ...(filters.search && { szukaj: filters.search }),
            ...(filters.format && { forma: filters.format }),
            ...(filters.profession && { zawod: filters.profession }),
            ...(filters.voivodeship && { wojewodztwo: filters.voivodeship }),
            ...(filters.price && { cena: filters.price }),
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const select = (field: keyof EventFilters, value: unknown): void => {
    apply({ [field]: value === noFilter ? '' : String(value) });
};

const clear = (): void => {
    search.value = '';
    apply({
        search: '',
        format: '',
        profession: '',
        voivodeship: '',
        price: '',
    });
};

// Waits for a pause in typing, so a word sends one request instead of one per letter.
const applySearch = useDebounceFn(() => {
    if (search.value !== props.filters.search) {
        apply({});
    }
}, 300);

watch(search, applySearch);

const fields = computed(() => [
    {
        name: 'format' as const,
        placeholder: 'All formats',
        items: props.options.formats,
    },
    {
        name: 'profession' as const,
        placeholder: 'All professions',
        items: props.options.professions,
    },
    {
        name: 'voivodeship' as const,
        placeholder: 'All voivodeships',
        items: props.options.voivodeships,
    },
    {
        name: 'price' as const,
        placeholder: 'Any price',
        items: props.options.prices,
    },
]);
</script>

<template>
    <div
        class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
        data-test="event-filters"
    >
        <Input
            v-model="search"
            type="search"
            :placeholder="$t('Search by title or city')"
            :aria-label="$t('Search by title or city')"
            data-test="event-filter-search"
        />

        <Select
            v-for="field in fields"
            :key="field.name"
            :model-value="filters[field.name] || noFilter"
            @update:model-value="select(field.name, $event)"
        >
            <SelectTrigger
                class="w-full"
                :aria-label="$t(field.placeholder)"
                :data-test="`event-filter-${field.name}`"
            >
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem :value="noFilter">
                    {{ $t(field.placeholder) }}
                </SelectItem>
                <SelectItem
                    v-for="item in field.items"
                    :key="item.value"
                    :value="item.value"
                >
                    {{ item.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Button
            v-if="hasFilters"
            variant="ghost"
            class="justify-self-start"
            data-test="event-filter-clear"
            @click="clear"
        >
            {{ $t('Clear filters') }}
        </Button>
    </div>
</template>
