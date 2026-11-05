<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { RotateCcw } from '@lucide/vue';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import RestoreEventController from '@/actions/App/Http/Controllers/Admin/RestoreEventController';
import { Button } from '@/components/ui/button';
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
import type { EventResource } from '@/types';

const props = defineProps<{
    events: EventResource[];
    withSections?: boolean;
}>();

const { formatDateRange } = useDateTimeFormat();

const startsSection = (index: number): boolean =>
    index === 0 ||
    props.events[index - 1].is_past !== props.events[index].is_past;
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>{{ $t('Title') }}</TableHead>
                <TableHead>{{ $t('Date') }}</TableHead>
                <TableHead>{{ $t('Format') }}</TableHead>
                <TableHead class="text-right">
                    <span class="sr-only">{{ $t('Actions') }}</span>
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <template v-for="(event, rowIndex) in events" :key="event.id">
                <TableRow
                    v-if="withSections && startsSection(rowIndex)"
                    class="bg-muted/50 hover:bg-muted/50"
                >
                    <TableCell
                        :colspan="4"
                        class="font-medium text-muted-foreground"
                    >
                        {{
                            event.is_past
                                ? $t('Past events')
                                : $t('Upcoming events')
                        }}
                    </TableCell>
                </TableRow>
                <TableRow>
                    <TableCell>
                        <div class="flex items-baseline justify-between gap-4">
                            <Link
                                v-if="!event.deleted_at"
                                :href="EventController.edit(event.id)"
                                class="font-medium hover:underline"
                            >
                                {{ event.title }}
                            </Link>
                            <span v-else class="font-medium">
                                {{ event.title }}
                            </span>
                            <span
                                v-if="event.city"
                                class="text-xs text-muted-foreground"
                            >
                                {{ event.city }}
                            </span>
                        </div>
                    </TableCell>
                    <TableCell class="whitespace-nowrap">
                        {{ formatDateRange(event.starts_at, event.ends_at) }}
                    </TableCell>
                    <TableCell>{{ event.format_label }}</TableCell>
                    <TableCell class="text-right">
                        <Form
                            v-if="event.deleted_at"
                            v-bind="RestoreEventController.form(event.id)"
                            v-slot="{ processing }"
                        >
                            <Button
                                type="submit"
                                variant="outline"
                                size="sm"
                                :disabled="processing"
                            >
                                <RotateCcw class="size-4" />
                                {{ $t('Restore') }}
                            </Button>
                        </Form>
                        <Button v-else variant="ghost" size="sm" as-child>
                            <Link :href="EventController.edit(event.id)">
                                {{ $t('Edit') }}
                            </Link>
                        </Button>
                    </TableCell>
                </TableRow>
            </template>
            <TableEmpty v-if="events.length === 0" :colspan="4">
                {{ $t('No events found.') }}
            </TableEmpty>
        </TableBody>
    </Table>
</template>
