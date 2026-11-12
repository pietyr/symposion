<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BenefitResource, SelectOption } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const props = defineProps<{
    action: RouteFormDefinition<'post'>;
    benefit?: BenefitResource;
    types: SelectOption[];
    attendances: SelectOption[];
    isHybrid: boolean;
    title: string;
    triggerLabel: string;
    triggerVariant?: 'default' | 'ghost';
    testId: string;
}>();

// A select item cannot have an empty value, so "every participant" needs its own.
const everyone = 'all';

const open = ref(false);
const type = ref(props.benefit?.type ?? 'checkbox');
const attendance = ref(props.benefit?.attendance ?? everyone);
const takesOneOption = computed(() => type.value !== 'checkbox');
const idPrefix = `benefit_${props.benefit?.id ?? 'new'}`;

const reset = (): void => {
    open.value = false;
    type.value = props.benefit?.type ?? 'checkbox';
    attendance.value = props.benefit?.attendance ?? everyone;
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button
                :variant="triggerVariant ?? 'default'"
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
                @success="reset"
            >
                <DialogHeader>
                    <DialogTitle>{{ title }}</DialogTitle>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label :for="`${idPrefix}_name`">{{
                        $t('Benefit name')
                    }}</Label>
                    <Input
                        :id="`${idPrefix}_name`"
                        name="name"
                        :default-value="benefit?.name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label :for="`${idPrefix}_type`">{{
                        $t('Field type')
                    }}</Label>
                    <Select v-model="type" name="type">
                        <SelectTrigger
                            :id="`${idPrefix}_type`"
                            class="w-full"
                            data-test="benefit-type"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in types"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.type" />
                </div>

                <div v-if="takesOneOption" class="flex items-center gap-3">
                    <Checkbox
                        :id="`${idPrefix}_is_required`"
                        name="is_required"
                        value="1"
                        :default-value="benefit?.is_required ?? false"
                    />
                    <Label :for="`${idPrefix}_is_required`">{{
                        $t('Choice required')
                    }}</Label>
                </div>

                <div v-if="isHybrid" class="grid gap-2">
                    <Label :for="`${idPrefix}_attendance`">{{
                        $t('Available to')
                    }}</Label>
                    <Select v-model="attendance">
                        <SelectTrigger
                            :id="`${idPrefix}_attendance`"
                            class="w-full"
                            data-test="benefit-attendance"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="everyone">
                                {{ $t('All participants') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in attendances"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <input
                        type="hidden"
                        name="attendance"
                        :value="attendance === everyone ? '' : attendance"
                    />
                    <InputError :message="errors.attendance" />
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
