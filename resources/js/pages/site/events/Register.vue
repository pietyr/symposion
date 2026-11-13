<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import EventPlaceBadges from '@/components/site/events/EventPlaceBadges.vue';
import BenefitField from '@/components/site/registrations/BenefitField.vue';
import ConsentField from '@/components/site/registrations/ConsentField.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { usePriceFormat } from '@/composables/usePriceFormat';
import { show } from '@/routes/events';
import { store } from '@/routes/events/registration';
import type {
    ConsentResource,
    PublicBenefitResource,
    PublicEventResource,
} from '@/types';

type AttendanceOption = {
    value: string;
    label: string;
    price: number;
    seats_left: number | null;
};

const props = defineProps<{
    event: PublicEventResource;
    attendances: AttendanceOption[];
    benefits: PublicBenefitResource[];
    consents: ConsentResource[];
}>();

const { formatDateTimeRange } = useDateTimeFormat();
const { formatPrice } = usePriceFormat();

const available = computed(() =>
    props.attendances.filter((option) => option.seats_left !== 0),
);

// With a single way of taking part there is nothing to choose.
const attendance = ref(
    available.value.length === 1 ? available.value[0].value : '',
);

const selected = computed(() =>
    props.attendances.find((option) => option.value === attendance.value),
);

const choices = ref<Record<number, string | string[]>>(
    Object.fromEntries(
        props.benefits.map((benefit) => [
            benefit.id,
            benefit.type === 'checkbox' ? [] : '',
        ]),
    ),
);

// A field meant for one kind of attendance shows up once that one is chosen.
const visibleBenefits = computed(() =>
    props.benefits.filter(
        (benefit) =>
            benefit.attendance === null ||
            benefit.attendance === attendance.value,
    ),
);

const total = computed(() => {
    if (!selected.value) {
        return null;
    }

    return visibleBenefits.value.reduce((sum, benefit) => {
        const chosen = ([] as string[]).concat(choices.value[benefit.id]);

        return (
            sum +
            benefit.options
                .filter((option) => chosen.includes(String(option.id)))
                .reduce((optionSum, option) => optionSum + option.price, 0)
        );
    }, selected.value.price);
});
</script>

<template>
    <Head :title="$t('Registration for :title', { title: event.title })" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-8">
        <div class="flex flex-col gap-3">
            <h1 class="text-3xl font-semibold tracking-tight">
                {{ $t('Registration for the event') }}
            </h1>
            <Link
                :href="show({ event: event.id, slug: event.slug })"
                class="text-lg font-medium hover:underline"
            >
                {{ event.title }}
            </Link>
            <div
                class="flex flex-wrap items-center gap-3 text-muted-foreground"
            >
                <span>{{
                    formatDateTimeRange(event.starts_at, event.ends_at)
                }}</span>
                <EventPlaceBadges :event="event" />
            </div>
        </div>

        <Form
            v-bind="store.form({ event: event.id, slug: event.slug })"
            class="flex flex-col gap-6"
            v-slot="{ errors, processing }"
        >
            <fieldset class="flex flex-col gap-3">
                <legend class="mb-3 font-medium">{{ $t('Attendance') }}</legend>

                <label
                    v-for="option in attendances"
                    :key="option.value"
                    class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border p-4 has-checked:border-primary has-checked:ring-1 has-checked:ring-primary has-disabled:cursor-not-allowed has-disabled:opacity-50"
                    :data-test="`attendance-${option.value}`"
                >
                    <span class="flex items-center gap-3">
                        <input
                            v-model="attendance"
                            type="radio"
                            name="attendance"
                            :value="option.value"
                            :disabled="option.seats_left === 0"
                            class="size-4 accent-primary"
                        />
                        <span class="flex flex-col">
                            <span class="font-medium">{{ option.label }}</span>
                            <span
                                v-if="option.seats_left !== null"
                                class="text-sm text-muted-foreground"
                            >
                                {{
                                    option.seats_left === 0
                                        ? $t('No seats left')
                                        : $t(':count seats left', {
                                              count: String(option.seats_left),
                                          })
                                }}
                            </span>
                        </span>
                    </span>
                    <span class="font-medium tabular-nums">{{
                        formatPrice(option.price)
                    }}</span>
                </label>

                <InputError :message="errors.attendance" />
            </fieldset>

            <BenefitField
                v-for="benefit in visibleBenefits"
                :key="benefit.id"
                v-model="choices[benefit.id]"
                :benefit="benefit"
                :error="errors[`benefits.${benefit.id}`]"
            />

            <fieldset v-if="consents.length > 0" class="flex flex-col gap-3">
                <legend class="mb-3 font-medium">{{ $t('Consents') }}</legend>
                <ConsentField
                    v-for="consent in consents"
                    :key="consent.id"
                    :consent="consent"
                    :error="errors[`consents.${consent.id}`]"
                />
            </fieldset>

            <div
                class="flex flex-wrap items-center justify-between gap-4 border-t pt-6"
            >
                <p class="text-lg">
                    {{ $t('To pay') }}:
                    <span
                        class="font-semibold tabular-nums"
                        data-test="registration-total"
                        >{{ total === null ? '—' : formatPrice(total) }}</span
                    >
                </p>
                <Button
                    size="lg"
                    :disabled="processing || !selected"
                    data-test="confirm-registration-button"
                >
                    {{ $t('Confirm registration') }}
                </Button>
            </div>
        </Form>
    </div>
</template>
