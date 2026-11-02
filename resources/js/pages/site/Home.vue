<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head :title="$t('Medical conferences')" />

    <section class="flex flex-col gap-2">
        <h1 class="text-3xl font-semibold tracking-tight">
            {{ $t('Medical conferences') }}
        </h1>
        <p class="text-muted-foreground">
            {{ $t('Upcoming conferences will be listed here.') }}
        </p>
    </section>
</template>
