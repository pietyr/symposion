export type EventFormat = 'online' | 'in_person' | 'hybrid';

export type EventResource = {
    id: number;
    title: string;
    slug: string;
    format: EventFormat;
    format_label: string;
    voivodeship: string | null;
    city: string | null;
    venue: string | null;
    starts_at: string;
    ends_at: string;
    is_published: boolean;
    deleted_at: string | null;
};
