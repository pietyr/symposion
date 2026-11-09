<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import RegistrationStatusBadge from '@/components/admin/registrations/RegistrationStatusBadge.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePriceFormat } from '@/composables/usePriceFormat';
import { index } from '@/routes/admin/registrations';
import type { ParticipantResource, RegistrationResource } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Registrations',
                href: index(),
            },
            {
                title: 'Registration details',
            },
        ],
    },
});

const props = defineProps<{
    registration: RegistrationResource;
    participant: ParticipantResource;
}>();

const { formatPrice } = usePriceFormat();
const dateFormatter = new Intl.DateTimeFormat('pl', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Europe/Warsaw',
});

const formatDate = (iso: string | null): string =>
    iso ? dateFormatter.format(new Date(iso)) : '—';

// Only the fields the participant's profession uses are filled in.
const profileRows = computed(() =>
    [
        { label: 'Profession', value: props.participant.profession_label },
        { label: 'Your profession', value: props.participant.profession_name },
        {
            label: 'License number (PWZ)',
            value: props.participant.license_number,
        },
        { label: 'University', value: props.participant.university },
        {
            label: 'Student ID number',
            value: props.participant.student_number,
        },
        { label: 'Company', value: props.participant.company },
    ].filter((row) => row.value),
);
</script>

<template>
    <Head :title="$t('Registration details')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <Heading
                :title="participant.name"
                :description="registration.event.title"
            />
            <RegistrationStatusBadge
                :status="registration.status"
                :label="registration.status_label"
                class="mb-8"
                data-test="registration-status"
            />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>{{ $t('Registration information') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl
                        class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm"
                    >
                        <dt class="text-muted-foreground">{{ $t('Event') }}</dt>
                        <dd>
                            <Link
                                :href="
                                    EventController.edit(registration.event.id)
                                "
                                class="underline underline-offset-4"
                            >
                                {{ registration.event.title }}
                            </Link>
                            <Badge
                                v-if="registration.event.is_deleted"
                                variant="secondary"
                                class="ml-2"
                            >
                                {{ $t('Event deleted') }}
                            </Badge>
                        </dd>
                        <dt class="text-muted-foreground">
                            {{ $t('Attendance') }}
                        </dt>
                        <dd>{{ registration.attendance_label }}</dd>
                        <dt class="text-muted-foreground">
                            {{ $t('Amount') }}
                        </dt>
                        <dd class="tabular-nums">
                            {{ formatPrice(registration.amount) }}
                        </dd>
                        <dt class="text-muted-foreground">
                            {{ $t('Registered at') }}
                        </dt>
                        <dd>{{ formatDate(registration.created_at) }}</dd>
                        <dt class="text-muted-foreground">
                            {{ $t('Confirmed at') }}
                        </dt>
                        <dd>{{ formatDate(registration.confirmed_at) }}</dd>
                    </dl>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ $t('Participant') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl
                        class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm"
                        data-test="participant-profile"
                    >
                        <dt class="text-muted-foreground">
                            {{ $t('Email address') }}
                        </dt>
                        <dd>
                            <a
                                :href="`mailto:${participant.email}`"
                                class="underline underline-offset-4"
                                >{{ participant.email }}</a
                            >
                        </dd>
                        <template v-for="row in profileRows" :key="row.label">
                            <dt class="text-muted-foreground">
                                {{ $t(row.label) }}
                            </dt>
                            <dd>{{ row.value }}</dd>
                        </template>
                        <template v-if="participant.specializations.length">
                            <dt class="text-muted-foreground">
                                {{ $t('Specializations') }}
                            </dt>
                            <dd class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="specialization in participant.specializations"
                                    :key="specialization"
                                    variant="outline"
                                >
                                    {{ specialization }}
                                </Badge>
                            </dd>
                        </template>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
