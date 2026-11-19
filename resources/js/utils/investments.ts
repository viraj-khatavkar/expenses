import currencyFormatter from './currencyFormatter';

export type Direction = 'deposit' | 'redemption';

export interface InvestmentTransaction {
    id: number;
    category: { id: number; name: string };
    direction: Direction;
    amount_paise: number;
    date: string | null;
    period: string;
    note: string | null;
}

export interface InvestmentSummary {
    period: string;
    depositedPaise: number;
    redeemedPaise: number;
    netPaise: number;
    targetPaise: number | null;
}

export function rupees(paise: number): string {
    return currencyFormatter(paise / 100);
}
