<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import CancelRegistrationController from '@/actions/App/Http/Controllers/Admin/CancelRegistrationController';
import ConfirmRegistrationController from '@/actions/App/Http/Controllers/Admin/ConfirmRegistrationController';
import RegistrationActionDialog from '@/components/registrations/RegistrationActionDialog.vue';
import RegistrationStatusBadge from '@/components/registrations/RegistrationStatusBadge.vue';
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
            <div class="mb-8 flex flex-wrap gap-2 sm:ml-auto">
                <RegistrationActionDialog
                    v-if="registration.status === 'pending'"
                    :action="
                        ConfirmRegistrationController.form(registration.id)
                    "
                    :label="$t('Confirm payment')"
                    :title="
                        $t('Confirm the payment of :amount?', {
                            amount: formatPrice(registration.amount),
                        })
                    "
                    :description="
                        $t('The registration will be marked as paid.')
                    "
                    test-id="confirm-payment"
                />
                <RegistrationActionDialog
                    v-if="registration.status !== 'cancelled'"
                    :action="CancelRegistrationController.form(registration.id)"
                    :label="$t('Cancel registration')"
                    :title="
                        $t('Cancel the registration of :name?', {
                            name: participant.name,
                        })
                    "
                    :description="
                        registration.status === 'paid'
                            ? $t(
                                  'The seat will be freed. A refund of the payment has to be made outside the system.',
                              )
                            : $t('The seat will be freed.')
                    "
                    variant="destructive"
                    test-id="cancel-registration"
                />
            </div>
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
                        <template v-if="registration.benefits?.length">
                            <dt class="text-muted-foreground">
                                {{ $t('Benefits') }}
                            </dt>
                            <dd>
                                <ul
                                    class="flex flex-col gap-1"
                                    data-test="registration-benefits"
                                >
                                    <li
                                        v-for="benefit in registration.benefits"
                                        :key="benefit.id"
                                    >
                                        {{ benefit.benefit_name }}:
                                        {{ benefit.option_name }}
                                        <span
                                            class="text-muted-foreground tabular-nums"
                                            >({{
                                                formatPrice(benefit.price)
                                            }})</span
                                        >
                                    </li>
                                </ul>
                            </dd>
                        </template>
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

            <Card v-if="registration.consents" class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>{{ $t('Given consents') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p
                        v-if="registration.consents.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        {{ $t('No consents given.') }}
                    </p>
                    <ul
                        v-else
                        class="flex flex-col gap-3 text-sm"
                        data-test="registration-consents"
                    >
                        <li
                            v-for="consent in registration.consents"
                            :key="consent.id"
                            class="flex flex-col gap-1"
                        >
                            <!-- eslint-disable-next-line vue/no-v-html -- sanitized when saved (SanitizeEventContent) -->
                            <div
                                class="[&_a]:underline [&_a]:underline-offset-2"
                                v-html="consent.content"
                            />
                            <span class="text-muted-foreground">
                                {{
                                    $t('Given :date', {
                                        date: formatDate(consent.given_at),
                                    })
                                }}
                            </span>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
