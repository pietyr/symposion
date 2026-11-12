<script setup lang="ts">
import { Check, CircleCheck, Copy } from '@lucide/vue';
import { useClipboard } from '@vueuse/core';
import RegistrationStatusBadge from '@/components/registrations/RegistrationStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { usePriceFormat } from '@/composables/usePriceFormat';
import type { MyRegistrationResource } from '@/types';

defineProps<{
    registration: MyRegistrationResource;
}>();

const { formatDateTime } = useDateTimeFormat();
const { formatPrice } = usePriceFormat();
const { copy, copied } = useClipboard();
</script>

<template>
    <section
        class="flex flex-col gap-4 rounded-lg border border-t-4 border-t-(color:--event-mark) p-5"
        data-test="my-registration"
    >
        <div class="flex flex-wrap items-center gap-3">
            <CircleCheck
                class="size-5 text-emerald-700 dark:text-emerald-500"
            />
            <h3 class="text-lg font-semibold">
                {{ $t('Your registration') }}
            </h3>
            <RegistrationStatusBadge
                :status="registration.status"
                :label="registration.status_label"
            />
        </div>

        <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm">
            <dt class="text-muted-foreground">{{ $t('Attendance') }}</dt>
            <dd>{{ registration.attendance_label }}</dd>
            <dt class="text-muted-foreground">{{ $t('Amount') }}</dt>
            <dd class="tabular-nums">{{ formatPrice(registration.amount) }}</dd>
            <template v-if="registration.created_at">
                <dt class="text-muted-foreground">
                    {{ $t('Registered at') }}
                </dt>
                <dd>{{ formatDateTime(registration.created_at) }}</dd>
            </template>
        </dl>

        <div
            v-if="registration.transfer"
            class="flex flex-col gap-3 rounded-md bg-muted/50 p-4"
            data-test="transfer-details"
        >
            <h4 class="font-medium">{{ $t('Transfer details') }}</h4>
            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm">
                <dt class="text-muted-foreground">{{ $t('Recipient') }}</dt>
                <dd>{{ registration.transfer.recipient }}</dd>
                <dt class="text-muted-foreground">
                    {{ $t('Account number') }}
                </dt>
                <dd class="flex items-center gap-2">
                    <span class="font-mono tabular-nums">{{
                        registration.transfer.account_number
                    }}</span>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="-my-1.5 shrink-0"
                        :aria-label="$t('Copy the account number')"
                        @click="copy(registration.transfer.account_number)"
                    >
                        <Check v-if="copied" class="size-4" />
                        <Copy v-else class="size-4" />
                    </Button>
                </dd>
                <dt class="text-muted-foreground">{{ $t('Amount') }}</dt>
                <dd class="tabular-nums">
                    {{ formatPrice(registration.transfer.amount) }}
                </dd>
                <dt class="text-muted-foreground">
                    {{ $t('Transfer title') }}
                </dt>
                <dd>{{ registration.transfer.title }}</dd>
            </dl>
            <p class="text-sm text-muted-foreground">
                {{
                    $t(
                        'The registration will be confirmed once the payment arrives.',
                    )
                }}
            </p>
        </div>
    </section>
</template>
