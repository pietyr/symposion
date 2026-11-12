<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePriceFormat } from '@/composables/usePriceFormat';
import type { BenefitOptionResource } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const props = defineProps<{
    action: RouteFormDefinition<'post'>;
    benefitId: number;
    option?: BenefitOptionResource;
    title: string;
    triggerLabel: string;
    triggerVariant?: 'outline' | 'ghost';
    testId: string;
}>();

const { toPriceInput } = usePriceFormat();

const open = ref(false);
const idPrefix = `option_${props.option?.id ?? `new_${props.benefitId}`}`;
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button
                :variant="triggerVariant ?? 'outline'"
                size="sm"
                :data-test="testId"
            >
                {{ triggerLabel }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="action"
                :options="{ preserveScroll: true, preserveState: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>{{ title }}</DialogTitle>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label :for="`${idPrefix}_name`">{{
                        $t('Option name')
                    }}</Label>
                    <Input
                        :id="`${idPrefix}_name`"
                        name="name"
                        :default-value="option?.name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label :for="`${idPrefix}_price`">{{
                            $t('Price (PLN)')
                        }}</Label>
                        <Input
                            :id="`${idPrefix}_price`"
                            name="price"
                            inputmode="decimal"
                            placeholder="0,00"
                            :default-value="toPriceInput(option?.price)"
                        />
                        <InputError :message="errors.price" />
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`${idPrefix}_seat_limit`">{{
                            $t('Seat limit')
                        }}</Label>
                        <Input
                            :id="`${idPrefix}_seat_limit`"
                            name="seat_limit"
                            type="number"
                            min="1"
                            inputmode="numeric"
                            :placeholder="$t('No limit')"
                            :default-value="option?.seat_limit ?? undefined"
                        />
                        <InputError :message="errors.seat_limit" />
                    </div>
                </div>

                <div class="grid gap-3">
                    <div class="flex items-center gap-3">
                        <Checkbox
                            :id="`${idPrefix}_is_disabled`"
                            name="is_disabled"
                            value="1"
                            :default-value="option?.is_disabled ?? false"
                        />
                        <Label :for="`${idPrefix}_is_disabled`">{{
                            $t('Shown, but cannot be chosen')
                        }}</Label>
                    </div>
                    <div class="flex items-center gap-3">
                        <Checkbox
                            :id="`${idPrefix}_is_hidden`"
                            name="is_hidden"
                            value="1"
                            :default-value="option?.is_hidden ?? false"
                        />
                        <Label :for="`${idPrefix}_is_hidden`">{{
                            $t('Hidden in the registration form')
                        }}</Label>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">{{ $t('Cancel') }}</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="processing"
                        :data-test="`${testId}-save`"
                    >
                        {{ $t('Save') }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
