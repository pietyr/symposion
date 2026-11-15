<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { ParticipantResource } from '@/types';

const props = defineProps<{
    participant: ParticipantResource;
}>();

// Only the fields the participant's profession uses are filled in.
const profileRows = computed(() =>
    [
        { label: 'Profession', value: props.participant.profession_label },
        { label: 'Your profession', value: props.participant.profession_name },
        {
            label: 'License number (PWZ)',
            value: props.participant.license_number,
        },
        { label: 'University', value: props.participant.university },
        {
            label: 'Student ID number',
            value: props.participant.student_number,
        },
        { label: 'Company', value: props.participant.company },
    ].filter((row) => row.value),
);
</script>

<template>
    <dl
        class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm"
        data-test="participant-profile"
    >
        <dt class="text-muted-foreground">
            {{ $t('Email address') }}
        </dt>
        <dd>
            <a
                :href="`mailto:${participant.email}`"
                class="underline underline-offset-4"
                >{{ participant.email }}</a
            >
        </dd>
        <template v-for="row in profileRows" :key="row.label">
            <dt class="text-muted-foreground">
                {{ $t(row.label) }}
            </dt>
            <dd>{{ row.value }}</dd>
        </template>
        <template v-if="participant.specializations.length">
            <dt class="text-muted-foreground">
                {{ $t('Specializations') }}
            </dt>
            <dd class="flex flex-wrap gap-1">
                <Badge
                    v-for="specialization in participant.specializations"
                    :key="specialization"
                    variant="outline"
                >
                    {{ specialization }}
                </Badge>
            </dd>
        </template>
    </dl>
</template>
