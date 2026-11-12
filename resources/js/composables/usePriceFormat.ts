import { usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

export type UsePriceFormatReturn = {
    formatPrice: (grosze: number) => string;
    toPriceInput: (grosze?: number | null) => string;
};

export function usePriceFormat(): UsePriceFormatReturn {
    const formatter = new Intl.NumberFormat(usePage().props.locale, {
        style: 'currency',
        currency: 'PLN',
    });

    return {
        formatPrice: (grosze) =>
            grosze === 0 ? trans('Free') : formatter.format(grosze / 100),

        // The value of a price field, typed the Polish way: 1500,50.
        toPriceInput: (grosze) =>
            grosze === null || grosze === undefined
                ? ''
                : (grosze / 100).toFixed(2).replace('.', ','),
    };
}
