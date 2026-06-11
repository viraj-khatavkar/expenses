export interface Category {
    id: bigint;
    name: string;
    expenses_count?: number;
    expenses_total?: number | null;
}
