<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SpecializationController from '@/actions/App/Http/Controllers/Admin/SpecializationController';
import SpecializationForm from '@/components/admin/specializations/SpecializationForm.vue';
import Heading from '@/components/Heading.vue';
import { create, index } from '@/routes/admin/specializations';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Specializations',
                href: index(),
            },
            {
                title: 'Add specialization',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="$t('Add specialization')" />

    <div class="p-4">
        <Heading :title="$t('Add specialization')" />

        <SpecializationForm
            :action="SpecializationController.store.form()"
            :submit-label="$t('Create specialization')"
        />
    </div>
</template>
