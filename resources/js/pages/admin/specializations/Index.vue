<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import SpecializationController from '@/actions/App/Http/Controllers/Admin/SpecializationController';
import Heading from '@/components/Heading.vue';
import PaginationControls from '@/components/PaginationControls.vue';
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
import { index } from '@/routes/admin/specializations';
import type { Paginated, SpecializationResource } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Specializations',
                href: index(),
            },
        ],
    },
});

defineProps<{
    specializations: Paginated<SpecializationResource>;
}>();
</script>

<template>
    <Head :title="$t('Specializations')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading :title="$t('Specializations')" />
            <Button as-child>
                <Link :href="SpecializationController.create()">
                    <Plus class="size-4" />
                    {{ $t('Add specialization') }}
                </Link>
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ $t('Specialization name') }}</TableHead>
                    <TableHead class="text-right">
                        <span class="sr-only">{{ $t('Actions') }}</span>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="specialization in specializations.data"
                    :key="specialization.id"
                >
                    <TableCell class="font-medium">
                        {{ specialization.name }}
                    </TableCell>
                    <TableCell class="text-right">
                        <Button variant="ghost" size="sm" as-child>
                            <Link
                                :href="
                                    SpecializationController.edit(
                                        specialization.id,
                                    )
                                "
                            >
                                {{ $t('Edit') }}
                            </Link>
                        </Button>
                    </TableCell>
                </TableRow>
                <TableEmpty
                    v-if="specializations.data.length === 0"
                    :colspan="2"
                >
                    {{ $t('No specializations found.') }}
                </TableEmpty>
            </TableBody>
        </Table>

        <PaginationControls :paginator="specializations" />
    </div>
</template>
