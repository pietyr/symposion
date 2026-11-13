<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import RegistrationController from '@/actions/App/Http/Controllers/Admin/RegistrationController';
import RegistrationStatusBadge from '@/components/registrations/RegistrationStatusBadge.vue';
import Heading from '@/components/Heading.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/admin/events';
import type { Paginated, RegistrationResource } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Events',
                href: index(),
            },
            {
                title: 'Participants',
            },
        ],
    },
});

defineProps<{
    event: { id: number; title: string; is_deleted: boolean };
    registrations: Paginated<RegistrationResource>;
}>();
</script>

<template>
    <Head :title="`${$t('Participants')}: ${event.title}`" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start gap-3">
            <Heading
                :title="event.title"
                :description="`${$t('Participants')}: ${registrations.meta.total}`"
            />
            <Badge v-if="event.is_deleted" variant="secondary" class="mt-1">
                {{ $t('Event deleted') }}
            </Badge>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ $t('Participant') }}</TableHead>
                    <TableHead>{{ $t('Profession') }}</TableHead>
                    <TableHead>{{ $t('Attendance') }}</TableHead>
                    <TableHead>{{ $t('Benefits') }}</TableHead>
                    <TableHead>{{ $t('Status') }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="registration in registrations.data"
                    :key="registration.id"
                    data-test="participant-row"
                >
                    <TableCell>
                        <Link
                            :href="RegistrationController.show(registration.id)"
                            class="font-medium hover:underline"
                        >
                            {{ registration.user.name }}
                        </Link>
                        <div class="text-xs text-muted-foreground">
                            {{ registration.user.email }}
                        </div>
                    </TableCell>
                    <TableCell>
                        {{ registration.user.profession_label ?? '—' }}
                    </TableCell>
                    <TableCell>{{ registration.attendance_label }}</TableCell>
                    <TableCell class="whitespace-normal">
                        <ul class="flex flex-col gap-0.5 text-sm">
                            <li
                                v-for="benefit in registration.benefits"
                                :key="benefit.id"
                            >
                                {{ benefit.benefit_name }}:
                                {{ benefit.option_name }}
                            </li>
                        </ul>
                    </TableCell>
                    <TableCell>
                        <RegistrationStatusBadge
                            :status="registration.status"
                            :label="registration.status_label"
                        />
                    </TableCell>
                </TableRow>
                <TableEmpty v-if="registrations.data.length === 0" :colspan="5">
                    {{ $t('No participants yet.') }}
                </TableEmpty>
            </TableBody>
        </Table>

        <PaginationControls :paginator="registrations" />
    </div>
</template>
