export default function formatCurrencyAmount(amount: number | string, currency: string): string {
    const numeric = Number(amount);
    const isWholeNumber = Number.isInteger(numeric);
    const locale = currency === 'USD' ? 'en-US' : 'en-IN';

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: isWholeNumber ? 0 : 2,
        maximumFractionDigits: isWholeNumber ? 0 : 2,
    }).format(numeric);
}
