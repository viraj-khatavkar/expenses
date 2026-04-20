import { Source } from '@/types/app/Models/Source';

export interface Income {
    id: bigint;
    date: string;
    amount: number;
    source_id: bigint;
    source: Source;
}

export interface IncomeMonthGroup {
    month: string;
    total: number;
    incomes: Income[];
}

export interface FinancialYearOption {
    year: number;
    label: string;
}
