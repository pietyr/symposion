<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import EventForm from '@/components/admin/events/EventForm.vue';
import Heading from '@/components/Heading.vue';
import { create, index } from '@/routes/admin/events';
import type { SelectOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Events',
                href: index(),
            },
            {
                title: 'Add event',
                href: create(),
            },
        ],
    },
});

defineProps<{
    formats: SelectOption[];
    voivodeships: SelectOption[];
    professions: SelectOption[];
    specializations: SelectOption[];
}>();
</script>

<template>
    <Head :title="$t('Add event')" />

    <div class="p-4">
        <Heading :title="$t('Add event')" />

        <EventForm
            :action="EventController.store.form()"
            :formats="formats"
            :voivodeships="voivodeships"
            :professions="professions"
            :specializations="specializations"
            :submit-label="$t('Create event')"
        />
    </div>
</template>
