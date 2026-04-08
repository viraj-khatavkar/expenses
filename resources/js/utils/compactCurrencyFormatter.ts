export default function compactCurrencyFormatter(amount: number): string {
    if (amount >= 100000) {
        return '₹' + parseFloat((amount / 100000).toFixed(2)) + 'L';
    }

    if (amount >= 1000) {
        return '₹' + parseFloat((amount / 1000).toFixed(2)) + 'K';
    }

    return '₹' + Math.round(amount).toString();
}
