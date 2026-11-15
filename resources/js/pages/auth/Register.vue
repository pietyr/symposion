<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import AcademicTitleField from '@/components/profile/AcademicTitleField.vue';
import ProfessionFields from '@/components/profile/ProfessionFields.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { ProfessionOption, SelectOption } from '@/types';

defineProps<{
    passwordRules: string;
    professions: ProfessionOption[];
    specializations: SelectOption[];
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
    },
});
</script>

<template>
    <Head :title="$t('Register')" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="first_name">{{ $t('First name') }}</Label>
                    <Input
                        id="first_name"
                        type="text"
                        required
                        v-focus
                        autocomplete="given-name"
                        name="first_name"
                        :placeholder="$t('First name')"
                    />
                    <InputError :message="errors.first_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="last_name">{{ $t('Last name') }}</Label>
                    <Input
                        id="last_name"
                        type="text"
                        required
                        autocomplete="family-name"
                        name="last_name"
                        :placeholder="$t('Last name')"
                    />
                    <InputError :message="errors.last_name" />
                </div>
            </div>

            <AcademicTitleField :error="errors.academic_title" />

            <div class="grid gap-2">
                <Label for="email">{{ $t('Email address') }}</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <ProfessionFields
                :professions="professions"
                :specializations="specializations"
                :errors="errors"
            />

            <div class="grid gap-2">
                <Label for="password">{{ $t('Password') }}</Label>
                <PasswordInput
                    id="password"
                    required
                    autocomplete="new-password"
                    name="password"
                    :placeholder="$t('Password')"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">{{
                    $t('Confirm password')
                }}</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    name="password_confirmation"
                    :placeholder="$t('Confirm password')"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                {{ $t('Create account') }}
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            {{ $t('Already have an account?') }}
            <TextLink :href="login()" class="underline underline-offset-4">{{
                $t('Log in')
            }}</TextLink>
        </div>
    </Form>
</template>
