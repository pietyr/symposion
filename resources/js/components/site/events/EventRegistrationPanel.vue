<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Info } from '@lucide/vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { edit as editProfile } from '@/routes/account/profile';
import { create as createRegistration } from '@/routes/events/registration';
import type { EventRegistrationState, PublicEventResource } from '@/types';

defineProps<{
    event: Pick<PublicEventResource, 'id' | 'slug'>;
    registration: EventRegistrationState;
}>();

const { formatDateTime } = useDateTimeFormat();
</script>

<template>
    <div
        v-if="['open', 'sign_in_required'].includes(registration.availability)"
        class="flex flex-wrap items-center gap-x-4 gap-y-2"
    >
        <Button size="lg" as-child>
            <Link
                :href="
                    createRegistration({ event: event.id, slug: event.slug })
                "
                data-test="register-button"
            >
                {{ $t('Sign up for the event') }}
            </Link>
        </Button>
        <span class="text-sm text-muted-foreground">
            {{
                $t('Registration is open until :date.', {
                    date: formatDateTime(registration.closes_at),
                })
            }}
        </span>
    </div>

    <Alert v-else data-test="registration-unavailable">
        <Info />
        <AlertDescription>
            <p>
                {{
                    registration.availability === 'not_open_yet' &&
                    registration.opens_at
                        ? $t('Registration opens on :date.', {
                              date: formatDateTime(registration.opens_at),
                          })
                        : registration.message
                }}
            </p>
            <p v-if="registration.availability === 'profession_missing'">
                <Link
                    :href="editProfile()"
                    class="font-medium text-foreground underline underline-offset-4"
                >
                    {{ $t('Go to the profile settings') }}
                </Link>
            </p>
        </AlertDescription>
    </Alert>
</template>
