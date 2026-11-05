<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SpecializationController from '@/actions/App/Http/Controllers/Admin/SpecializationController';
import DeleteSpecializationDialog from '@/components/admin/specializations/DeleteSpecializationDialog.vue';
import SpecializationForm from '@/components/admin/specializations/SpecializationForm.vue';
import Heading from '@/components/Heading.vue';
import { Separator } from '@/components/ui/separator';
import { index } from '@/routes/admin/specializations';
import type { SpecializationResource } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Specializations',
                href: index(),
            },
            {
                title: 'Edit specialization',
                href: index(),
            },
        ],
    },
});

defineProps<{
    specialization: SpecializationResource;
}>();
</script>

<template>
    <Head :title="specialization.name" />

    <div class="p-4">
        <Heading
            :title="specialization.name"
            :description="$t('Edit specialization')"
        />

        <SpecializationForm
            :key="specialization.id"
            :action="SpecializationController.update.form(specialization.id)"
            :specialization="specialization"
            :submit-label="$t('Save')"
        />

        <Separator class="my-8 max-w-2xl" />

        <DeleteSpecializationDialog :specialization="specialization" />
    </div>
</template>
