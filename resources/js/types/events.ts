export type EventFormat = 'online' | 'in_person' | 'hybrid';

export type EventResource = {
    id: number;
    title: string;
    invoice_title: string | null;
    slug: string;
    format: EventFormat;
    format_label: string;
    voivodeship: string | null;
    city: string | null;
    venue: string | null;
    starts_at: string;
    ends_at: string;
    price_online: number | null;
    price_in_person: number | null;
    is_visible: boolean;
    is_promoted: boolean;
    is_past: boolean;
    deleted_at: string | null;
};

export type PublicEventResource = {
    id: number;
    title: string;
    slug: string;
    format_label: string;
    city: string | null;
    venue: string | null;
    starts_at: string;
    ends_at: string;
};
