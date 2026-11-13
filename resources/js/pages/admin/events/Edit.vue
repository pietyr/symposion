<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ExternalLink, Users } from '@lucide/vue';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import EventParticipantController from '@/actions/App/Http/Controllers/Admin/EventParticipantController';
import EventBenefits from '@/components/admin/benefits/EventBenefits.vue';
import EventConsents from '@/components/admin/consents/EventConsents.vue';
import DeleteEventDialog from '@/components/admin/events/DeleteEventDialog.vue';
import EventForm from '@/components/admin/events/EventForm.vue';
import EventSections from '@/components/admin/events/EventSections.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { TabsContent, TabsTrigger } from '@/components/ui/tabs';
import { index } from '@/routes/admin/events';
import { show } from '@/routes/events';
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
    professions: SelectOption[];
    specializations: SelectOption[];
    benefitTypes: SelectOption[];
    benefitAttendances: SelectOption[];
}>();
</script>

<template>
    <Head :title="event.title" />

    <div class="p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading :title="event.title" :description="$t('Edit event')" />
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link
                        :href="EventParticipantController.index(event.id)"
                        data-test="event-participants-link"
                    >
                        <Users class="size-4" />
                        {{ $t('Participants') }}
                    </Link>
                </Button>
                <Button variant="outline" as-child>
                    <a
                        :href="show.url({ event: event.id, slug: event.slug })"
                        target="_blank"
                        rel="noopener"
                        data-test="view-event-link"
                    >
                        <ExternalLink class="size-4" />
                        {{ $t('View on website') }}
                    </a>
                </Button>
            </div>
        </div>

        <EventForm
            :key="event.id"
            :action="EventController.update.form(event.id)"
            :event="event"
            :formats="formats"
            :voivodeships="voivodeships"
            :professions="professions"
            :specializations="specializations"
            :submit-label="$t('Save')"
        >
            <template #tab-triggers>
                <TabsTrigger value="content" data-test="event-tab-content">
                    {{ $t('Content') }}
                </TabsTrigger>
                <TabsTrigger value="benefits" data-test="event-tab-benefits">
                    {{ $t('Benefits') }}
                </TabsTrigger>
                <TabsTrigger value="consents" data-test="event-tab-consents">
                    {{ $t('Consents') }}
                </TabsTrigger>
            </template>
            <template #tabs>
                <TabsContent value="content">
                    <EventSections
                        :event-id="event.id"
                        :sections="event.sections ?? []"
                    />
                </TabsContent>
                <TabsContent value="benefits">
                    <EventBenefits
                        :event-id="event.id"
                        :is-hybrid="event.format === 'hybrid'"
                        :benefits="event.benefits ?? []"
                        :types="benefitTypes"
                        :attendances="benefitAttendances"
                    />
                </TabsContent>
                <TabsContent value="consents">
                    <EventConsents
                        :event-id="event.id"
                        :consents="event.consents ?? []"
                    />
                </TabsContent>
            </template>
        </EventForm>

        <Separator class="my-8 max-w-2xl" />

        <DeleteEventDialog :event="event" />
    </div>
</template>
