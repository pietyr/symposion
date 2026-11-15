<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import RegistrationController from '@/actions/App/Http/Controllers/Admin/RegistrationController';
import ProfileDetails from '@/components/admin/users/ProfileDetails.vue';
import Heading from '@/components/Heading.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import RegistrationStatusBadge from '@/components/registrations/RegistrationStatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePriceFormat } from '@/composables/usePriceFormat';
import { index } from '@/routes/admin/users';
import type {
    Paginated,
    ParticipantResource,
    RegistrationResource,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index(),
            },
            {
                title: 'User details',
            },
        ],
    },
});

const props = defineProps<{
    account: ParticipantResource;
    registrations: Paginated<RegistrationResource>;
}>();

const { formatPrice } = usePriceFormat();
const dateTimeFormatter = new Intl.DateTimeFormat('pl', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Europe/Warsaw',
});
const dateFormatter = new Intl.DateTimeFormat('pl', {
    dateStyle: 'short',
    timeStyle: 'short',
    timeZone: 'Europe/Warsaw',
});

const formatDateTime = (iso: string | null): string =>
    iso ? dateTimeFormatter.format(new Date(iso)) : '—';

const titledName = computed(() =>
    [props.account.academic_title, props.account.name]
        .filter(Boolean)
        .join(' '),
);
</script>

<template>
    <Head :title="$t('User details')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <Heading :title="titledName" />
            <Badge v-if="account.is_admin" variant="secondary" class="mb-8">
                {{ $t('Administrator') }}
            </Badge>
            <Badge
                v-if="!account.email_verified_at"
                variant="outline"
                class="mb-8"
                data-test="account-unverified"
            >
                {{ $t('Unverified') }}
            </Badge>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>{{ $t('Profile') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <ProfileDetails :participant="account" />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ $t('Account') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl
                        class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm"
                    >
                        <dt class="text-muted-foreground">
                            {{ $t('Signed up at') }}
                        </dt>
                        <dd>{{ formatDateTime(account.created_at) }}</dd>
                        <dt class="text-muted-foreground">
                            {{ $t('Email verified at') }}
                        </dt>
                        <dd>
                            {{
                                account.email_verified_at
                                    ? formatDateTime(account.email_verified_at)
                                    : $t('Unverified')
                            }}
                        </dd>
                    </dl>
                </CardContent>
            </Card>
        </div>

        <div class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">{{ $t('Registrations') }}</h2>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ $t('Event') }}</TableHead>
                        <TableHead>{{ $t('Attendance') }}</TableHead>
                        <TableHead class="text-right">{{
                            $t('Amount')
                        }}</TableHead>
                        <TableHead>{{ $t('Status') }}</TableHead>
                        <TableHead>{{ $t('Registered at') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="registration in registrations.data"
                        :key="registration.id"
                        data-test="user-registration-row"
                    >
                        <TableCell class="max-w-72 whitespace-normal">
                            <Link
                                :href="
                                    RegistrationController.show(registration.id)
                                "
                                class="font-medium hover:underline"
                            >
                                {{ registration.event.title }}
                            </Link>
                            <Badge
                                v-if="registration.event.is_deleted"
                                variant="secondary"
                                class="ml-1"
                            >
                                {{ $t('Event deleted') }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{
                            registration.attendance_label
                        }}</TableCell>
                        <TableCell class="text-right tabular-nums">
                            {{ formatPrice(registration.amount) }}
                        </TableCell>
                        <TableCell>
                            <RegistrationStatusBadge
                                :status="registration.status"
                                :label="registration.status_label"
                            />
                        </TableCell>
                        <TableCell class="text-muted-foreground tabular-nums">
                            {{
                                registration.created_at
                                    ? dateFormatter.format(
                                          new Date(registration.created_at),
                                      )
                                    : ''
                            }}
                        </TableCell>
                    </TableRow>
                    <TableEmpty
                        v-if="registrations.data.length === 0"
                        :colspan="5"
                    >
                        {{ $t('No registrations yet.') }}
                    </TableEmpty>
                </TableBody>
            </Table>

            <PaginationControls :paginator="registrations" />
        </div>
    </div>
</template>
