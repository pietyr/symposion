<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import EventSectionController from '@/actions/App/Http/Controllers/Admin/EventSectionController';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { EventSectionResource } from '@/types';

defineProps<{
    eventId: number;
    sections: EventSectionResource[];
}>();

const formOptions = { preserveScroll: true, preserveState: true };
</script>

<template>
    <div class="space-y-8">
        <p v-if="sections.length === 0" class="text-sm text-muted-foreground">
            {{ $t('No sections yet.') }}
        </p>

        <section
            v-for="section in sections"
            :key="`${section.id}-${section.updated_at}`"
            class="space-y-4 rounded-lg border p-4"
            data-test="event-section"
        >
            <Form
                v-bind="
                    EventSectionController.update.form({
                        event: eventId,
                        section: section.id,
                    })
                "
                :options="formOptions"
                class="space-y-4"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label :for="`section_title_${section.id}`">{{
                        $t('Title')
                    }}</Label>
                    <Input
                        :id="`section_title_${section.id}`"
                        name="title"
                        :default-value="section.title"
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label :for="`section_content_${section.id}`">{{
                        $t('Content (HTML)')
                    }}</Label>
                    <Textarea
                        :id="`section_content_${section.id}`"
                        name="content"
                        rows="10"
                        class="font-mono text-sm"
                        :default-value="section.content"
                    />
                    <InputError :message="errors.content" />
                </div>

                <Button
                    type="submit"
                    size="sm"
                    :disabled="processing"
                    data-test="save-section-button"
                >
                    {{ $t('Save') }}
                </Button>
            </Form>

            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="ghost" size="sm">
                        {{ $t('Delete section') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="
                            EventSectionController.destroy.form({
                                event: eventId,
                                section: section.id,
                            })
                        "
                        :options="formOptions"
                        class="space-y-6"
                        v-slot="{ processing }"
                    >
                        <DialogHeader>
                            <DialogTitle>
                                {{
                                    $t('Delete section ":title"?', {
                                        title: section.title,
                                    })
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
                            >
                                {{ $t('Delete section') }}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </section>

        <Form
            v-bind="EventSectionController.store.form(eventId)"
            :options="formOptions"
            reset-on-success
            class="space-y-4 rounded-lg border border-dashed p-4"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="new_section_title">{{ $t('Title') }}</Label>
                <Input id="new_section_title" name="title" />
                <InputError :message="errors.title" />
            </div>

            <div class="grid gap-2">
                <Label for="new_section_content">{{
                    $t('Content (HTML)')
                }}</Label>
                <Textarea
                    id="new_section_content"
                    name="content"
                    rows="6"
                    class="font-mono text-sm"
                />
                <InputError :message="errors.content" />
            </div>

            <Button
                type="submit"
                size="sm"
                :disabled="processing"
                data-test="add-section-button"
            >
                {{ $t('Add section') }}
            </Button>
        </Form>
    </div>
</template>
