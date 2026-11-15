import type { SelectOption } from './ui';

export type AdminUserResource = {
    id: number;
    name: string;
    email: string;
    profession_label: string | null;
    is_admin: boolean;
    is_verified: boolean;
    is_blocked: boolean;
    is_deleted: boolean;
    registrations_count: number;
    created_at: string | null;
};

export type UserFilters = {
    profession: string;
    state: string;
    search: string;
    trashed: boolean;
};

export type UserFilterOptions = {
    professions: SelectOption[];
    states: SelectOption[];
};
