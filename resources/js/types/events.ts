import type { SelectOption } from './ui';

export type EventFormat = 'online' | 'in_person' | 'hybrid';

export type EventSectionResource = {
    id: number;
    title: string;
    content: string;
    updated_at: string | null;
};

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
    professions?: string[];
    specializations?: string[];
    sections?: EventSectionResource[];
    is_visible: boolean;
    is_promoted: boolean;
    primary_color: string | null;
    accent_color: string | null;
    large_banner_url: string | null;
    small_banner_url: string | null;
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
    price_online: number | null;
    price_in_person: number | null;
    large_banner_url: string | null;
    small_banner_url: string | null;
    default_banner: number;
    professions?: string[];
    specializations?: string[];
    sections?: EventSectionResource[];
};

export type EventFilters = {
    format: string;
    profession: string;
    voivodeship: string;
    price: string;
    search: string;
};

export type EventFilterOptions = {
    formats: SelectOption[];
    professions: SelectOption[];
    voivodeships: SelectOption[];
    prices: SelectOption[];
};

export type AdminEventFilters = EventFilters & {
    visibility: string;
    trashed: boolean;
    sort: string;
};

export type AdminEventFilterOptions = EventFilterOptions & {
    visibilities: SelectOption[];
};
