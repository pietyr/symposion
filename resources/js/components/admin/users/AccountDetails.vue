<script setup lang="ts">
import type { ParticipantResource } from '@/types';

defineProps<{
    account: Pick<
        ParticipantResource,
        'created_at' | 'email_verified_at' | 'blocked_at'
    >;
}>();

const dateTimeFormatter = new Intl.DateTimeFormat('pl', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Europe/Warsaw',
});

const formatDateTime = (iso: string | null): string =>
    iso ? dateTimeFormatter.format(new Date(iso)) : '—';
</script>

<template>
    <dl
        class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm"
        data-test="account-details"
    >
        <dt class="text-muted-foreground">
            {{ $t('Signed up at') }}
        </dt>
        <dd>{{ formatDateTime(account.created_at) }}</dd>
        <dt class="text-muted-foreground">
            {{ $t('Email verified at') }}
        </dt>
        <dd>
            {{
                account.email_verified_at
                    ? formatDateTime(account.email_verified_at)
                    : $t('Unverified')
            }}
        </dd>
        <template v-if="account.blocked_at">
            <dt class="text-muted-foreground">
                {{ $t('Blocked at') }}
            </dt>
            <dd>{{ formatDateTime(account.blocked_at) }}</dd>
        </template>
    </dl>
</template>
