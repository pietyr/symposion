export type BenefitType = 'checkbox' | 'radio' | 'select';

export type BenefitResource = {
    id: number;
    name: string;
    type: BenefitType;
    type_label: string;
    is_required: boolean;
    attendance: 'online' | 'in_person' | null;
    attendance_label: string | null;
    updated_at: string | null;
};
