<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import EventConsentController from '@/actions/App/Http/Controllers/Admin/EventConsentController';
import MoveEventConsentController from '@/actions/App/Http/Controllers/Admin/MoveEventConsentController';
import ConsentDialog from '@/components/admin/consents/ConsentDialog.vue';
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
import type { ConsentResource } from '@/types';

defineProps<{
    eventId: number;
    consents: ConsentResource[];
}>();
</script>

<template>
    <div class="space-y-4">
        <p v-if="consents.length === 0" class="text-sm text-muted-foreground">
            {{ $t('No consents yet.') }}
        </p>

        <section
            v-for="(consent, index) in consents"
            :key="`${consent.id}-${consent.updated_at}`"
            class="space-y-3 rounded-lg border p-4"
            data-test="event-consent"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <Badge :variant="consent.is_required ? 'default' : 'outline'">
                    {{
                        consent.is_required
                            ? $t('Required consent')
                            : $t('Optional consent')
                    }}
                </Badge>

                <div class="flex shrink-0 items-center gap-1">
                    <ConsentDialog
                        :action="
                            EventConsentController.update.form({
                                event: eventId,
                                consent: consent.id,
                            })
                        "
                        :consent="consent"
                        :title="$t('Edit consent')"
                        :trigger-label="$t('Edit')"
                        trigger-variant="ghost"
                        test-id="edit-consent"
                    />

                    <Dialog>
                        <DialogTrigger as-child>
                            <Button
                                variant="ghost"
                                size="sm"
                                data-test="delete-consent"
                            >
                                {{ $t('Delete') }}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <Form
                                v-bind="
                                    EventConsentController.destroy.form({
                                        event: eventId,
                                        consent: consent.id,
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
                                        {{ $t('Delete this consent?') }}
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
                                        data-test="delete-consent-confirm"
                                    >
                                        {{ $t('Delete consent') }}
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>

                    <MoveButtons
                        :action="
                            MoveEventConsentController.form({
                                event: eventId,
                                consent: consent.id,
                            })
                        "
                        :is-first="index === 0"
                        :is-last="index === consents.length - 1"
                        :up-label="$t('Move consent up')"
                        :down-label="$t('Move consent down')"
                        test-id="move-consent"
                    />
                </div>
            </div>

            <!-- eslint-disable-next-line vue/no-v-html -- sanitized when saved (SanitizeEventContent) -->
            <div
                class="text-sm [&_a]:underline [&_a]:underline-offset-2"
                v-html="consent.content"
            />
        </section>

        <ConsentDialog
            :action="EventConsentController.store.form(eventId)"
            :title="$t('New consent')"
            :trigger-label="$t('Add consent')"
            test-id="add-consent"
        />
    </div>
</template>
