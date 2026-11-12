<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import BenefitOptionController from '@/actions/App/Http/Controllers/Admin/BenefitOptionController';
import MoveBenefitOptionController from '@/actions/App/Http/Controllers/Admin/MoveBenefitOptionController';
import BenefitOptionDialog from '@/components/admin/benefits/BenefitOptionDialog.vue';
import MoveButtons from '@/components/admin/MoveButtons.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePriceFormat } from '@/composables/usePriceFormat';
import type { BenefitOptionResource } from '@/types';

const props = defineProps<{
    eventId: number;
    benefitId: number;
    options: BenefitOptionResource[];
}>();

const { formatPrice } = usePriceFormat();

const route = (option: BenefitOptionResource) => ({
    event: props.eventId,
    benefit: props.benefitId,
    option: option.id,
});
</script>

<template>
    <div class="space-y-3">
        <Table v-if="options.length > 0">
            <TableHeader>
                <TableRow>
                    <TableHead>{{ $t('Option') }}</TableHead>
                    <TableHead class="text-right">{{ $t('Price') }}</TableHead>
                    <TableHead class="text-right">
                        {{ $t('Seat limit') }}
                    </TableHead>
                    <TableHead class="text-right">
                        <span class="sr-only">{{ $t('Actions') }}</span>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="(option, index) in options"
                    :key="`${option.id}-${option.updated_at}`"
                    data-test="benefit-option"
                >
                    <TableCell class="whitespace-normal">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                :class="{
                                    'text-muted-foreground':
                                        option.is_disabled || option.is_hidden,
                                }"
                                >{{ option.name }}</span
                            >
                            <Badge v-if="option.is_disabled" variant="outline">
                                {{ $t('Disabled') }}
                            </Badge>
                            <Badge v-if="option.is_hidden" variant="outline">
                                {{ $t('Hidden') }}
                            </Badge>
                        </div>
                    </TableCell>
                    <TableCell class="text-right tabular-nums">
                        {{ formatPrice(option.price) }}
                    </TableCell>
                    <TableCell class="text-right tabular-nums">
                        {{ option.seat_limit ?? $t('No limit') }}
                    </TableCell>
                    <TableCell>
                        <div class="flex items-center justify-end gap-1">
                            <BenefitOptionDialog
                                :action="
                                    BenefitOptionController.update.form(
                                        route(option),
                                    )
                                "
                                :benefit-id="benefitId"
                                :option="option"
                                :title="$t('Edit option')"
                                :trigger-label="$t('Edit')"
                                trigger-variant="ghost"
                                test-id="edit-benefit-option"
                            />

                            <Dialog>
                                <DialogTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        data-test="delete-benefit-option"
                                    >
                                        {{ $t('Delete') }}
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <Form
                                        v-bind="
                                            BenefitOptionController.destroy.form(
                                                route(option),
                                            )
                                        "
                                        :options="{
                                            preserveScroll: true,
                                            preserveState: true,
                                        }"
                                        class="space-y-6"
                                        v-slot="{ processing }"
                                    >
                                        <DialogHeader>
                                            <DialogTitle>
                                                {{
                                                    $t(
                                                        'Delete option ":name"?',
                                                        { name: option.name },
                                                    )
                                                }}
                                            </DialogTitle>
                                        </DialogHeader>
                                        <DialogFooter class="gap-2">
                                            <DialogClose as-child>
                                                <Button variant="secondary">{{
                                                    $t('Cancel')
                                                }}</Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                :disabled="processing"
                                                data-test="delete-benefit-option-confirm"
                                            >
                                                {{ $t('Delete option') }}
                                            </Button>
                                        </DialogFooter>
                                    </Form>
                                </DialogContent>
                            </Dialog>

                            <MoveButtons
                                :action="
                                    MoveBenefitOptionController.form(
                                        route(option),
                                    )
                                "
                                :is-first="index === 0"
                                :is-last="index === options.length - 1"
                                :up-label="$t('Move option up')"
                                :down-label="$t('Move option down')"
                                test-id="move-benefit-option"
                            />
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <BenefitOptionDialog
            :action="
                BenefitOptionController.store.form({
                    event: eventId,
                    benefit: benefitId,
                })
            "
            :benefit-id="benefitId"
            :title="$t('New option')"
            :trigger-label="$t('Add option')"
            test-id="add-benefit-option"
        />
    </div>
</template>
