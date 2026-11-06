<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { EventFormat, EventResource, SelectOption } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const props = defineProps<{
    action: RouteFormDefinition<'post'>;
    formats: SelectOption[];
    voivodeships: SelectOption[];
    professions: SelectOption[];
    specializations: SelectOption[];
    submitLabel: string;
    event?: EventResource;
}>();

const format = ref<EventFormat>(props.event?.format ?? 'in_person');
const hasVenue = computed(() => format.value !== 'online');
const hasOnlineAttendance = computed(() => format.value !== 'in_person');

// A new event is open to everyone until the admin narrows it down.
const isProfessionChecked = (profession: string): boolean =>
    props.event?.professions?.includes(profession) ?? true;

const isSpecializationChecked = (specialization: string): boolean =>
    props.event?.specializations?.includes(specialization) ?? false;

const groszeToZlotys = (grosze?: number | null): string | undefined =>
    grosze === null || grosze === undefined
        ? undefined
        : (grosze / 100).toFixed(2).replace('.', ',');

const activeTab = ref('details');
const isFormTab = computed(() =>
    ['details', 'audience'].includes(activeTab.value),
);

// Every field lives in one form, so an error may sit on a tab that is not open.
const showFirstTabWithErrors = (errors: Record<string, string>): void => {
    const fields = Object.keys(errors);
    const isAudienceField = (field: string): boolean =>
        field.startsWith('professions') || field.startsWith('specializations');

    activeTab.value = fields.every(isAudienceField) ? 'audience' : 'details';
};

// ISO strings from the server carry the application's offset, so the first
// 16 characters are already the local "YYYY-MM-DDTHH:mm" the input expects.
const toDateTimeLocal = (iso?: string): string | undefined => iso?.slice(0, 16);
</script>

<template>
    <Tabs v-model="activeTab" class="max-w-2xl gap-6" :unmount-on-hide="false">
        <TabsList>
            <TabsTrigger value="details">{{ $t('Details') }}</TabsTrigger>
            <TabsTrigger value="audience" data-test="event-tab-audience">
                {{ $t('Professions and specializations') }}
            </TabsTrigger>
            <slot name="tab-triggers" />
        </TabsList>

        <Form
            v-bind="action"
            v-show="isFormTab"
            class="space-y-6"
            novalidate
            v-slot="{ errors, processing }"
            @error="showFirstTabWithErrors"
        >
            <TabsContent value="details" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="title">{{ $t('Title') }}</Label>
                    <Input
                        id="title"
                        name="title"
                        :default-value="event?.title"
                        required
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="format">{{ $t('Format') }}</Label>
                    <Select v-model="format" name="format">
                        <SelectTrigger id="format" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in formats"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.format" />
                </div>

                <template v-if="hasVenue">
                    <div class="grid gap-2">
                        <Label for="voivodeship">{{ $t('Voivodeship') }}</Label>
                        <Select
                            name="voivodeship"
                            :default-value="event?.voivodeship ?? undefined"
                        >
                            <SelectTrigger id="voivodeship" class="w-full">
                                <SelectValue
                                    :placeholder="$t('Select voivodeship')"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in voivodeships"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.voivodeship" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="city">{{ $t('City') }}</Label>
                            <Input
                                id="city"
                                name="city"
                                :default-value="event?.city ?? undefined"
                            />
                            <InputError :message="errors.city" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="venue">{{ $t('Venue') }}</Label>
                            <Input
                                id="venue"
                                name="venue"
                                :default-value="event?.venue ?? undefined"
                                :placeholder="$t('Venue name and address')"
                            />
                            <InputError :message="errors.venue" />
                        </div>
                    </div>
                </template>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="starts_at">{{ $t('Starts at') }}</Label>
                        <Input
                            id="starts_at"
                            name="starts_at"
                            type="datetime-local"
                            :default-value="toDateTimeLocal(event?.starts_at)"
                            required
                        />
                        <InputError :message="errors.starts_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ends_at">{{ $t('Ends at') }}</Label>
                        <Input
                            id="ends_at"
                            name="ends_at"
                            type="datetime-local"
                            :default-value="toDateTimeLocal(event?.ends_at)"
                            required
                        />
                        <InputError :message="errors.ends_at" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-if="hasOnlineAttendance" class="grid gap-2">
                        <Label for="price_online">{{
                            $t('Online price (PLN)')
                        }}</Label>
                        <Input
                            id="price_online"
                            name="price_online"
                            inputmode="decimal"
                            :default-value="groszeToZlotys(event?.price_online)"
                            required
                        />
                        <InputError :message="errors.price_online" />
                    </div>

                    <div v-if="hasVenue" class="grid gap-2">
                        <Label for="price_in_person">{{
                            $t('In-person price (PLN)')
                        }}</Label>
                        <Input
                            id="price_in_person"
                            name="price_in_person"
                            inputmode="decimal"
                            :default-value="
                                groszeToZlotys(event?.price_in_person)
                            "
                            required
                        />
                        <InputError :message="errors.price_in_person" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="invoice_title">{{ $t('Invoice title') }}</Label>
                    <Input
                        id="invoice_title"
                        name="invoice_title"
                        :default-value="event?.invoice_title ?? undefined"
                    />
                    <InputError :message="errors.invoice_title" />
                </div>

                <div class="flex items-center gap-3">
                    <Checkbox
                        id="is_hidden"
                        name="is_hidden"
                        value="1"
                        :default-value="!(event?.is_visible ?? false)"
                    />
                    <Label for="is_hidden">{{
                        $t('Hidden on the event list')
                    }}</Label>
                </div>

                <div class="flex items-center gap-3">
                    <Checkbox
                        id="is_promoted"
                        name="is_promoted"
                        value="1"
                        :default-value="event?.is_promoted ?? false"
                    />
                    <Label for="is_promoted">{{
                        $t('Promoted on the home page')
                    }}</Label>
                </div>
            </TabsContent>

            <TabsContent value="audience" class="space-y-6">
                <fieldset class="grid gap-3">
                    <legend class="mb-2 text-sm leading-none font-medium">
                        {{ $t('Professions') }}
                    </legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <div
                            v-for="profession in professions"
                            :key="profession.value"
                            class="flex items-center gap-3"
                        >
                            <Checkbox
                                :id="`profession_${profession.value}`"
                                name="professions[]"
                                :value="profession.value"
                                :default-value="
                                    isProfessionChecked(profession.value)
                                "
                            />
                            <Label :for="`profession_${profession.value}`">
                                {{ profession.label }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="errors.professions" />
                </fieldset>

                <fieldset class="grid gap-3">
                    <legend class="mb-2 text-sm leading-none font-medium">
                        {{ $t('Specializations') }}
                    </legend>
                    <div
                        class="grid max-h-64 gap-2 overflow-y-auto rounded-md border p-3 sm:grid-cols-2"
                    >
                        <div
                            v-for="specialization in specializations"
                            :key="specialization.value"
                            class="flex items-center gap-3"
                        >
                            <Checkbox
                                :id="`specialization_${specialization.value}`"
                                name="specializations[]"
                                :value="specialization.value"
                                :default-value="
                                    isSpecializationChecked(
                                        specialization.value,
                                    )
                                "
                            />
                            <Label
                                :for="`specialization_${specialization.value}`"
                            >
                                {{ specialization.label }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="errors.specializations" />
                </fieldset>
            </TabsContent>

            <Button :disabled="processing" data-test="save-event-button">
                {{ submitLabel }}
            </Button>
        </Form>

        <slot name="tabs" />
    </Tabs>
</template>
