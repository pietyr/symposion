<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
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
import type { RouteFormDefinition } from '@/wayfinder';

defineProps<{
    action: RouteFormDefinition<'post'>;
    label: string;
    title: string;
    description: string;
    variant?: 'default' | 'destructive';
    size?: 'default' | 'sm';
    testId: string;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button
                :variant="variant ?? 'default'"
                :size="size ?? 'default'"
                :data-test="testId"
            >
                {{ label }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="action"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>
                <InputError :message="errors.status" />

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">{{ $t('Back') }}</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :variant="variant ?? 'default'"
                        :disabled="processing"
                        :data-test="`${testId}-confirm`"
                    >
                        {{ label }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
