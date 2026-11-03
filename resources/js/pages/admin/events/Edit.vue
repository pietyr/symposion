<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import DeleteEventDialog from '@/components/admin/events/DeleteEventDialog.vue';
import EventForm from '@/components/admin/events/EventForm.vue';
import Heading from '@/components/Heading.vue';
import { Separator } from '@/components/ui/separator';
import { index } from '@/routes/admin/events';
import type { EventResource, SelectOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Events',
                href: index(),
            },
            {
                title: 'Edit event',
                href: index(),
            },
        ],
    },
});

defineProps<{
    event: EventResource;
    formats: SelectOption[];
    voivodeships: SelectOption[];
}>();
</script>

<template>
    <Head :title="event.title" />

    <div class="p-4">
        <Heading :title="event.title" :description="$t('Edit event')" />

        <EventForm
            :key="event.id"
            :action="EventController.update.form(event.id)"
            :event="event"
            :formats="formats"
            :voivodeships="voivodeships"
            :submit-label="$t('Save')"
        />

        <Separator class="my-8 max-w-2xl" />

        <DeleteEventDialog :event="event" />
    </div>
</template>
