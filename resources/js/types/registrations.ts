import type { SelectOption } from './ui';

export type RegistrationStatus = 'pending' | 'paid' | 'cancelled';

export type RegistrationResource = {
    id: number;
    event: { id: number; title: string; is_deleted: boolean };
    user: { id: number; name: string; email: string };
    attendance: 'online' | 'in_person';
    attendance_label: string;
    amount: number;
    status: RegistrationStatus;
    status_label: string;
    confirmed_at: string | null;
    created_at: string | null;
};

export type ParticipantResource = {
    id: number;
    name: string;
    email: string;
    profession_label: string | null;
    license_number: string | null;
    university: string | null;
    student_number: string | null;
    company: string | null;
    profession_name: string | null;
    specializations: string[];
};

export type MyRegistrationResource = {
    id: number;
    event?: {
        id: number;
        slug: string;
        title: string;
        starts_at: string;
        ends_at: string;
    };
    attendance_label: string;
    amount: number;
    status: RegistrationStatus;
    status_label: string;
    created_at: string | null;
    transfer: {
        recipient: string;
        account_number: string;
        title: string;
        amount: number;
    } | null;
};

export type EventRegistrationState = {
    availability:
        | 'open'
        | 'sign_in_required'
        | 'not_open_yet'
        | 'closed'
        | 'already_registered'
        | 'profession_missing'
        | 'profession_not_allowed'
        | 'full';
    message: string;
    opens_at: string | null;
    closes_at: string;
};

export type RegistrationFilters = {
    event: string;
    status: string;
    attendance: string;
    search: string;
};

export type RegistrationFilterOptions = {
    events: SelectOption[];
    statuses: SelectOption[];
    attendances: SelectOption[];
};
