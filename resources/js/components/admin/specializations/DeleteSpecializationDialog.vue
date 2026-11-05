<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import SpecializationController from '@/actions/App/Http/Controllers/Admin/SpecializationController';
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
import type { SpecializationResource } from '@/types';

defineProps<{
    specialization: SpecializationResource;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="destructive">
                {{ $t('Delete specialization') }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="
                    SpecializationController.destroy.form(specialization.id)
                "
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{
                            $t('Delete specialization ":name"?', {
                                name: specialization.name,
                            })
                        }}
                    </DialogTitle>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">{{ $t('Cancel') }}</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        {{ $t('Delete specialization') }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
