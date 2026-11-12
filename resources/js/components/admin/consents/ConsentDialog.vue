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
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { ConsentResource } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const props = defineProps<{
    action: RouteFormDefinition<'post'>;
    consent?: ConsentResource;
    title: string;
    triggerLabel: string;
    triggerVariant?: 'default' | 'ghost';
    testId: string;
}>();

const open = ref(false);
const idPrefix = `consent_${props.consent?.id ?? 'new'}`;
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
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>{{ title }}</DialogTitle>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label :for="`${idPrefix}_content`">{{
                        $t('Content (HTML)')
                    }}</Label>
                    <Textarea
                        :id="`${idPrefix}_content`"
                        name="content"
                        rows="5"
                        class="font-mono text-sm"
                        :default-value="consent?.content"
                    />
                    <InputError :message="errors.content" />
                </div>

                <div class="flex items-center gap-3">
                    <Checkbox
                        :id="`${idPrefix}_is_required`"
                        name="is_required"
                        value="1"
                        :default-value="consent?.is_required ?? false"
                    />
                    <Label :for="`${idPrefix}_is_required`">{{
                        $t('Required to register')
                    }}</Label>
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
