<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { SpecializationResource } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

defineProps<{
    action: RouteFormDefinition<'post'>;
    submitLabel: string;
    specialization?: SpecializationResource;
}>();
</script>

<template>
    <Form
        v-bind="action"
        class="max-w-2xl space-y-6"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-2">
            <Label for="name">{{ $t('Specialization name') }}</Label>
            <Input
                id="name"
                name="name"
                :default-value="specialization?.name"
                required
            />
            <InputError :message="errors.name" />
        </div>

        <Button :disabled="processing" data-test="save-specialization-button">
            {{ submitLabel }}
        </Button>
    </Form>
</template>
