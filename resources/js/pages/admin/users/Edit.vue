<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AcademicTitleField from '@/components/profile/AcademicTitleField.vue';
import ProfessionFields from '@/components/profile/ProfessionFields.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/admin/users';
import type {
    ProfessionalProfile,
    ProfessionOption,
    SelectOption,
    User,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index(),
            },
            {
                title: 'Edit user',
            },
        ],
    },
});

defineProps<{
    account: User;
    profile: ProfessionalProfile;
    professions: ProfessionOption[];
    specializations: SelectOption[];
}>();
</script>

<template>
    <Head :title="$t('Edit user')" />

    <div class="p-4">
        <Heading :title="account.name" :description="$t('Edit user')" />

        <Form
            v-bind="UserController.update.form(account.id)"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="first_name">{{ $t('First name') }}</Label>
                    <Input
                        id="first_name"
                        name="first_name"
                        :default-value="account.first_name"
                        required
                    />
                    <InputError :message="errors.first_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="last_name">{{ $t('Last name') }}</Label>
                    <Input
                        id="last_name"
                        name="last_name"
                        :default-value="account.last_name"
                        required
                    />
                    <InputError :message="errors.last_name" />
                </div>
            </div>

            <AcademicTitleField
                :default-value="profile.academic_title"
                :error="errors.academic_title"
            />

            <div class="grid gap-2">
                <Label for="email">{{ $t('Email address') }}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    :default-value="account.email"
                    required
                />
                <InputError :message="errors.email" />
            </div>

            <ProfessionFields
                :professions="professions"
                :specializations="specializations"
                :profile="profile"
                :errors="errors"
            />

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="save-user-button">
                    {{ $t('Save') }}
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="UserController.show(account.id)">
                        {{ $t('Cancel') }}
                    </Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
