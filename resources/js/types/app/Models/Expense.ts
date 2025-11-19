import { Category } from '@/types/app/Models/Category';

export interface Expense {
    id: bigint;
    date: string;
    amount: number;
    category_id: bigint;
    category: Category;
}
