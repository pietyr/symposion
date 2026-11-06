import { usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

export type UsePriceFormatReturn = {
    formatPrice: (grosze: number) => string;
};

export function usePriceFormat(): UsePriceFormatReturn {
    const formatter = new Intl.NumberFormat(usePage().props.locale, {
        style: 'currency',
        currency: 'PLN',
    });

    return {
        formatPrice: (grosze) =>
            grosze === 0 ? trans('Free') : formatter.format(grosze / 100),
    };
}
