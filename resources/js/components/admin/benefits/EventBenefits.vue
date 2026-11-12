<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import EventBenefitController from '@/actions/App/Http/Controllers/Admin/EventBenefitController';
import MoveEventBenefitController from '@/actions/App/Http/Controllers/Admin/MoveEventBenefitController';
import BenefitFieldDialog from '@/components/admin/benefits/BenefitFieldDialog.vue';
import BenefitOptions from '@/components/admin/benefits/BenefitOptions.vue';
import MoveButtons from '@/components/admin/MoveButtons.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { BenefitResource, SelectOption } from '@/types';

defineProps<{
    eventId: number;
    isHybrid: boolean;
    benefits: BenefitResource[];
    types: SelectOption[];
    attendances: SelectOption[];
}>();
</script>

<template>
    <div class="space-y-4">
        <p v-if="benefits.length === 0" class="text-sm text-muted-foreground">
            {{ $t('No benefits yet.') }}
        </p>

        <section
            v-for="(benefit, index) in benefits"
            :key="`${benefit.id}-${benefit.updated_at}`"
            class="space-y-3 rounded-lg border p-4"
            data-test="event-benefit"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex min-w-60 flex-1 flex-wrap items-center gap-2">
                    <h3 class="font-medium">{{ benefit.name }}</h3>
                    <Badge variant="secondary">{{ benefit.type_label }}</Badge>
                    <Badge v-if="benefit.is_required" variant="outline">
                        {{ $t('Choice required') }}
                    </Badge>
                    <Badge v-if="benefit.attendance_label" variant="outline">
                        {{ benefit.attendance_label }}
                    </Badge>
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    <BenefitFieldDialog
                        :action="
                            EventBenefitController.update.form({
                                event: eventId,
                                benefit: benefit.id,
                            })
                        "
                        :benefit="benefit"
                        :types="types"
                        :attendances="attendances"
                        :is-hybrid="isHybrid"
                        :title="$t('Edit benefit')"
                        :trigger-label="$t('Edit')"
                        trigger-variant="ghost"
                        test-id="edit-benefit"
                    />

                    <Dialog>
                        <DialogTrigger as-child>
                            <Button
                                variant="ghost"
                                size="sm"
                                data-test="delete-benefit"
                            >
                                {{ $t('Delete') }}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <Form
                                v-bind="
                                    EventBenefitController.destroy.form({
                                        event: eventId,
                                        benefit: benefit.id,
                                    })
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
                                            $t('Delete benefit ":name"?', {
                                                name: benefit.name,
                                            })
                                        }}
                                    </DialogTitle>
                                    <DialogDescription
                                        v-if="benefit.options?.length"
                                    >
                                        {{
                                            $t(
                                                'Its options will be deleted as well.',
                                            )
                                        }}
                                    </DialogDescription>
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
                                        data-test="delete-benefit-confirm"
                                    >
                                        {{ $t('Delete benefit') }}
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>

                    <MoveButtons
                        :action="
                            MoveEventBenefitController.form({
                                event: eventId,
                                benefit: benefit.id,
                            })
                        "
                        :is-first="index === 0"
                        :is-last="index === benefits.length - 1"
                        :up-label="$t('Move benefit up')"
                        :down-label="$t('Move benefit down')"
                        test-id="move-benefit"
                    />
                </div>
            </div>

            <BenefitOptions
                :event-id="eventId"
                :benefit-id="benefit.id"
                :options="benefit.options ?? []"
            />
        </section>

        <BenefitFieldDialog
            :action="EventBenefitController.store.form(eventId)"
            :types="types"
            :attendances="attendances"
            :is-hybrid="isHybrid"
            :title="$t('New benefit')"
            :trigger-label="$t('Add benefit')"
            test-id="add-benefit"
        />
    </div>
</template>
