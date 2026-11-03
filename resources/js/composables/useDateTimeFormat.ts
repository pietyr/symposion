import { usePage } from '@inertiajs/vue3';

// Event times belong to the venue, so they are shown in the application's
// time zone rather than the visitor's.
const timeZone = 'Europe/Warsaw';

export type UseDateTimeFormatReturn = {
    formatDateTimeRange: (start: string, end: string) => string;
};

export function useDateTimeFormat(): UseDateTimeFormatReturn {
    const formatter = new Intl.DateTimeFormat(usePage().props.locale, {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone,
    });

    return {
        formatDateTimeRange: (start, end) =>
            formatter.formatRange(new Date(start), new Date(end)),
    };
}
