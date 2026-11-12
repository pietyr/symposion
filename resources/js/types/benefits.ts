export type BenefitType = 'checkbox' | 'radio' | 'select';

export type BenefitOptionResource = {
    id: number;
    name: string;
    price: number;
    seat_limit: number | null;
    is_disabled: boolean;
    is_hidden: boolean;
    updated_at: string | null;
};

export type BenefitResource = {
    id: number;
    name: string;
    type: BenefitType;
    type_label: string;
    is_required: boolean;
    attendance: 'online' | 'in_person' | null;
    attendance_label: string | null;
    options?: BenefitOptionResource[];
    updated_at: string | null;
};
