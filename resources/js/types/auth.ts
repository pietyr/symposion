export type User = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    is_admin: boolean;
};

export type ProfessionOption = {
    value: string;
    label: string;
    license_number: 'required' | 'optional' | null;
    details: string[];
    specializations: boolean;
};

export type ProfessionalProfile = {
    profession: string | null;
    license_number: string | null;
    university: string | null;
    student_number: string | null;
    company: string | null;
    profession_name: string | null;
    specializations: string[];
};

export type Auth = {
    user: User | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
