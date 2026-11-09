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
