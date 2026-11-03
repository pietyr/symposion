<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
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
import type { EventResource } from '@/types';

defineProps<{
    event: EventResource;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="destructive" data-test="delete-event-button">
                {{ $t('Delete event') }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="EventController.destroy.form(event.id)"
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>
                        {{ $t('Delete ":title"?', { title: event.title }) }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            $t(
                                'The event will disappear from the website. You can restore it later from the list of deleted events.',
                            )
                        }}
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">{{ $t('Cancel') }}</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                        data-test="confirm-delete-event-button"
                    >
                        {{ $t('Delete event') }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
