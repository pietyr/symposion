<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import RegistrationController from '@/actions/App/Http/Controllers/Admin/RegistrationController';
import ParticipantCombobox from '@/components/admin/registrations/ParticipantCombobox.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePriceFormat } from '@/composables/usePriceFormat';
import { trans } from 'laravel-vue-i18n';
import { index } from '@/routes/admin/registrations';

type AttendanceOption = {
    value: string;
    label: string;
    price: number;
    seats_left: number | null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Registrations',
                href: index(),
            },
            {
                title: 'New registration',
            },
        ],
    },
});

const props = defineProps<{
    events: {
        value: string;
        label: string;
        attendances: AttendanceOption[];
    }[];
}>();

const { formatPrice } = usePriceFormat();

const eventId = ref('');
const attendance = ref('');
const amount = ref('');

const attendances = computed(
    () =>
        props.events.find((event) => event.value === eventId.value)
            ?.attendances ?? [],
);

const attendanceLabel = (option: AttendanceOption): string =>
    [
        option.label,
        formatPrice(option.price),
        option.seats_left === null
            ? null
            : trans(':count seats left', { count: String(option.seats_left) }),
    ]
        .filter(Boolean)
        .join(' · ');

// A single way of taking part needs no choice.
watch(eventId, () => {
    attendance.value =
        attendances.value.length === 1 ? attendances.value[0].value : '';
});

// The amount starts from the price; the admin may change it, e.g. for an invited guest.
watch(attendance, () => {
    const price = attendances.value.find(
        (option) => option.value === attendance.value,
    )?.price;

    amount.value =
        price === undefined ? '' : (price / 100).toFixed(2).replace('.', ',');
});
</script>

<template>
    <Head :title="$t('New registration')" />

    <div class="flex flex-col gap-6 p-4">
        <Heading :title="$t('New registration')" />

        <Form
            v-bind="RegistrationController.store.form()"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="event_id">{{ $t('Event') }}</Label>
                <Select v-model="eventId" name="event_id">
                    <SelectTrigger
                        id="event_id"
                        class="w-full"
                        data-test="event-select"
                    >
                        <SelectValue :placeholder="$t('Select an event')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="event in events"
                            :key="event.value"
                            :value="event.value"
                        >
                            {{ event.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.event_id" />
            </div>

            <div class="grid gap-2">
                <Label for="user_id">{{ $t('Participant') }}</Label>
                <ParticipantCombobox id="user_id" name="user_id" />
                <InputError :message="errors.user_id" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="attendance">{{ $t('Attendance') }}</Label>
                    <Select
                        v-model="attendance"
                        name="attendance"
                        :disabled="attendances.length === 0"
                    >
                        <SelectTrigger
                            id="attendance"
                            class="w-full"
                            data-test="attendance-select"
                        >
                            <SelectValue
                                :placeholder="$t('Select attendance')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in attendances"
                                :key="option.value"
                                :value="option.value"
                                :disabled="option.seats_left === 0"
                            >
                                {{ attendanceLabel(option) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.attendance" />
                </div>

                <div class="grid gap-2">
                    <Label for="amount">{{ $t('Amount (PLN)') }}</Label>
                    <Input
                        id="amount"
                        v-model="amount"
                        name="amount"
                        inputmode="decimal"
                    />
                    <InputError :message="errors.amount" />
                </div>
            </div>

            <Button :disabled="processing" data-test="save-registration-button">
                {{ $t('Add registration') }}
            </Button>
        </Form>
    </div>
</template>
