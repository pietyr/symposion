import type { ComputedRef, Ref } from 'vue';
import { computed, ref, watch } from 'vue';
import type { PublicBenefitResource } from '@/types';

export type BenefitChoices = Record<number, string | string[]>;

export type UseBenefitChoicesReturn = {
    choices: Ref<BenefitChoices>;
    visibleBenefits: ComputedRef<PublicBenefitResource[]>;
    optionsTotal: ComputedRef<number>;
};

const emptyChoices = (benefits: PublicBenefitResource[]): BenefitChoices =>
    Object.fromEntries(
        benefits.map((benefit) => [
            benefit.id,
            benefit.type === 'checkbox' ? [] : '',
        ]),
    );

/**
 * The benefit options chosen in a registration form and what they add to the amount.
 */
export function useBenefitChoices(
    benefits: Ref<PublicBenefitResource[]>,
    attendance: Ref<string>,
): UseBenefitChoicesReturn {
    const choices = ref<BenefitChoices>(emptyChoices(benefits.value));

    // Another event brings other fields, so the choices start over.
    watch(benefits, (fields) => {
        choices.value = emptyChoices(fields);
    });

    // A field meant for one kind of attendance shows up once that one is chosen.
    const visibleBenefits = computed(() =>
        benefits.value.filter(
            (benefit) =>
                benefit.attendance === null ||
                benefit.attendance === attendance.value,
        ),
    );

    const optionsTotal = computed(() =>
        visibleBenefits.value.reduce((sum, benefit) => {
            const chosen = ([] as string[]).concat(
                choices.value[benefit.id] ?? [],
            );

            return (
                sum +
                benefit.options
                    .filter((option) => chosen.includes(String(option.id)))
                    .reduce((optionSum, option) => optionSum + option.price, 0)
            );
        }, 0),
    );

    return { choices, visibleBenefits, optionsTotal };
}
