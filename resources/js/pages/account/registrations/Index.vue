<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CancelRegistrationController from '@/actions/App/Http/Controllers/Account/CancelRegistrationController';
import RegistrationActionDialog from '@/components/registrations/RegistrationActionDialog.vue';
import RegistrationStatusBadge from '@/components/registrations/RegistrationStatusBadge.vue';
import Heading from '@/components/Heading.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { usePriceFormat } from '@/composables/usePriceFormat';
import { show as showEvent } from '@/routes/events';
import { index } from '@/routes/account/registrations';
import type { MyRegistrationResource, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'My registrations',
                href: index(),
            },
        ],
    },
});

defineProps<{
    registrations: Paginated<MyRegistrationResource>;
}>();

const { formatDateRange } = useDateTimeFormat();
const { formatPrice } = usePriceFormat();
</script>

<template>
    <Head :title="$t('My registrations')" />

    <div class="flex flex-col gap-6 p-4">
        <Heading :title="$t('My registrations')" />

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ $t('Event') }}</TableHead>
                    <TableHead>{{ $t('Date') }}</TableHead>
                    <TableHead>{{ $t('Attendance') }}</TableHead>
                    <TableHead class="text-right">{{ $t('Amount') }}</TableHead>
                    <TableHead>{{ $t('Status') }}</TableHead>
                    <TableHead>
                        <span class="sr-only">{{ $t('Actions') }}</span>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="registration in registrations.data"
                    :key="registration.id"
                    data-test="my-registration-row"
                >
                    <TableCell class="max-w-80 whitespace-normal">
                        <Link
                            v-if="registration.event"
                            :href="
                                showEvent({
                                    event: registration.event.id,
                                    slug: registration.event.slug,
                                })
                            "
                            class="font-medium hover:underline"
                        >
                            {{ registration.event.title }}
                        </Link>
                    </TableCell>
                    <TableCell class="tabular-nums">
                        <template v-if="registration.event">
                            {{
                                formatDateRange(
                                    registration.event.starts_at,
                                    registration.event.ends_at,
                                )
                            }}
                        </template>
                    </TableCell>
                    <TableCell>{{ registration.attendance_label }}</TableCell>
                    <TableCell class="text-right tabular-nums">
                        {{ formatPrice(registration.amount) }}
                    </TableCell>
                    <TableCell>
                        <RegistrationStatusBadge
                            :status="registration.status"
                            :label="registration.status_label"
                        />
                    </TableCell>
                    <TableCell class="text-right">
                        <RegistrationActionDialog
                            v-if="registration.status === 'pending'"
                            :action="
                                CancelRegistrationController.form(
                                    registration.id,
                                )
                            "
                            :label="$t('Cancel registration')"
                            :title="
                                $t('Cancel your registration for :title?', {
                                    title: registration.event?.title ?? '',
                                })
                            "
                            :description="$t('The seat will be freed.')"
                            variant="destructive"
                            size="sm"
                            test-id="cancel-registration"
                        />
                    </TableCell>
                </TableRow>
                <TableEmpty v-if="registrations.data.length === 0" :colspan="6">
                    {{ $t('You have no registrations yet.') }}
                </TableEmpty>
            </TableBody>
        </Table>

        <PaginationControls :paginator="registrations" />
    </div>
</template>
