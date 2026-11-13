<script setup lang="ts">
import { onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import type { ConsentResource } from '@/types';

defineProps<{
    consent: ConsentResource;
    error?: string;
}>();

const content = ref<HTMLElement | null>(null);

// A link to the terms should not take the participant away from the half-filled form.
onMounted(() => {
    content.value?.querySelectorAll('a').forEach((link) => {
        link.target = '_blank';
        link.rel = 'noopener';
    });
});
</script>

<template>
    <div class="flex flex-col gap-1">
        <label
            class="flex cursor-pointer items-start gap-3"
            :data-test="`consent-${consent.id}`"
        >
            <input
                type="checkbox"
                name="consents[]"
                :value="consent.id"
                class="mt-1 size-4 shrink-0 accent-primary"
            />
            <span class="text-sm">
                <!-- eslint-disable-next-line vue/no-v-html -- sanitized when saved (SanitizeEventContent) -->
                <span
                    ref="content"
                    class="[&_a]:underline [&_a]:underline-offset-2 [&_p]:inline"
                    v-html="consent.content"
                />
                <span v-if="consent.is_required" class="text-muted-foreground">
                    ({{ $t('required') }})</span
                >
            </span>
        </label>
        <InputError :message="error" />
    </div>
</template>
