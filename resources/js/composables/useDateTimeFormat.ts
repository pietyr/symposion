import { usePage } from '@inertiajs/vue3';

// Event times belong to the venue, so they are shown in the application's
// time zone rather than the visitor's.
const timeZone = 'Europe/Warsaw';

export type UseDateTimeFormatReturn = {
    formatDateTimeRange: (start: string, end: string) => string;
    formatDateRange: (start: string, end: string) => string;
};

export function useDateTimeFormat(): UseDateTimeFormatReturn {
    const { locale } = usePage().props;

    const dateTimeFormatter = new Intl.DateTimeFormat(locale, {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone,
    });

    const dateFormatter = new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone,
    });

    const dateParts = (iso: string) => {
        const parts = dateFormatter.formatToParts(new Date(iso));
        const part = (type: Intl.DateTimeFormatPartTypes): string =>
            parts.find((candidate) => candidate.type === type)?.value ?? '';

        return {
            day: part('day'),
            month: part('month'),
            year: part('year'),
        };
    };

    return {
        formatDateTimeRange: (start, end) =>
            dateTimeFormatter.formatRange(new Date(start), new Date(end)),

        // 25.04.2027, 25–27.04.2027, 30.04–02.05.2027 or 30.12.2026–02.01.2027
        formatDateRange: (start, end) => {
            const from = dateParts(start);
            const to = dateParts(end);
            const toDate = `${to.day}.${to.month}.${to.year}`;

            if (from.year !== to.year) {
                return `${from.day}.${from.month}.${from.year}–${toDate}`;
            }

            if (from.month !== to.month) {
                return `${from.day}.${from.month}–${toDate}`;
            }

            return from.day === to.day ? toDate : `${from.day}–${toDate}`;
        },
    };
}
