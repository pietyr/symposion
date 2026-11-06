<script setup lang="ts">
import { computed } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    kind: 'banner' | 'thumbnail';
    url: string | null;
    defaultNumber: number;
    sizes: string;
}>();

const widths = {
    banner: [480, 768, 1280, 1920, 3840],
    thumbnail: [480, 768, 1280],
};

const srcset = (extension: string): string =>
    widths[props.kind]
        .map(
            (width) =>
                `/images/event-banners/${props.kind}-${props.defaultNumber}-${width}.${extension} ${width}w`,
        )
        .join(', ');

const fallback = computed(
    () => `/images/event-banners/${props.kind}-${props.defaultNumber}-768.webp`,
);
</script>

<template>
    <img v-if="url" :src="url" alt="" v-bind="$attrs" />
    <picture v-else>
        <source type="image/avif" :srcset="srcset('avif')" :sizes="sizes" />
        <img
            :src="fallback"
            :srcset="srcset('webp')"
            :sizes="sizes"
            alt=""
            v-bind="$attrs"
        />
    </picture>
</template>
