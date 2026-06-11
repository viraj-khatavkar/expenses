import { Category } from '@/types/app/Models/Category';

export interface Expense {
    id: bigint;
    date: string;
    amount: number;
    note: string | null;
    category_id: bigint;
    category: Category;
}

export interface ExpenseDayGroup {
    label: string;
    total: number;
    expenses: Expense[];
}
