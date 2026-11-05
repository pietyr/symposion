import { usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

export type UsePriceFormatReturn = {
    formatPrice: (grosze: number) => string;
    formatPriceRange: (...prices: Array<number | null>) => string;
};

export function usePriceFormat(): UsePriceFormatReturn {
    const formatter = new Intl.NumberFormat(usePage().props.locale, {
        style: 'currency',
        currency: 'PLN',
    });

    const formatPrice = (grosze: number): string =>
        grosze === 0 ? trans('Free') : formatter.format(grosze / 100);

    return {
        formatPrice,
        formatPriceRange: (...prices) => {
            const amounts = prices.filter((price) => price !== null);

            if (amounts.length === 0) {
                return '';
            }

            const lowest = Math.min(...amounts);
            const highest = Math.max(...amounts);

            return lowest === highest
                ? formatPrice(lowest)
                : `${formatPrice(lowest)} – ${formatPrice(highest)}`;
        },
    };
}
