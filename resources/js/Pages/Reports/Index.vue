<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Reports" />

        <PageHeader title="Reports" :subtitle="period.label">
            <label for="fy-switcher" class="sr-only">Financial Year</label>
            <select
                id="fy-switcher"
                v-model="selectedFy"
                @change="switchFy"
                class="rounded-md bg-white py-1.5 pr-8 pl-3 text-sm text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10"
            >
                <option v-for="option in availableFys" :key="option.year" :value="option.year">
                    {{ option.label }}
                </option>
            </select>
        </PageHeader>

        <div
            v-if="empty"
            class="rounded-xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Nothing recorded in {{ fyLabel }} for this period.
            </p>
        </div>

        <template v-else>
            <div data-testid="cashflow-summary" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div
                    class="min-w-0 rounded-xl bg-gray-50 px-5 py-5 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Income</p>
                    <p
                        class="mt-1 text-2xl font-bold break-words text-gray-900 tabular-nums dark:text-white"
                    >
                        {{ currencyFormatter(cashflow!.income) }}
                    </p>
                </div>
                <div
                    class="min-w-0 rounded-xl bg-gray-50 px-5 py-5 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Spent</p>
                    <p
                        class="mt-1 text-2xl font-bold break-words text-gray-900 tabular-nums dark:text-white"
                    >
                        {{ currencyFormatter(cashflow!.spent) }}
                    </p>
                    <p
                        v-if="cashflow!.avgMonthlySpend !== null"
                        class="mt-2 text-xs text-gray-500 dark:text-gray-400"
                    >
                        {{ currencyFormatter(cashflow!.avgMonthlySpend) }}/mo average
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ period.averageLabel }}
                    </p>
                </div>
                <div
                    class="min-w-0 rounded-xl bg-gray-50 px-5 py-5 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Net cashflow</p>
                    <p
                        class="mt-1 text-2xl font-bold break-words tabular-nums"
                        :class="balanceClass(cashflow!.net)"
                    >
                        {{ signedCurrency(cashflow!.net) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Income minus recorded expenses
                    </p>
                    <p
                        v-if="cashflow!.netRate !== null"
                        class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                    >
                        {{ cashflow!.netRate }}% of recorded income
                    </p>
                    <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        No income recorded in this period
                    </p>
                </div>
            </div>

            <section
                data-testid="spending-changes"
                class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Spending changes
                    </h2>
                </div>
                <div v-if="monthlyComparison" data-testid="monthly-comparison" class="px-5 py-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ monthlyComparison.currentLabel }} compared with
                        {{ monthlyComparison.previousLabel }}
                    </p>
                    <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">
                        {{ comparisonLabel(monthlyComparison) }}
                    </p>
                    <p
                        v-if="monthlyComparison.percentageChange !== null"
                        class="mt-1 text-sm"
                        :class="spendingChangeClass(monthlyComparison.difference)"
                    >
                        {{
                            percentageLabel(
                                monthlyComparison.percentageChange,
                                monthlyComparison.difference
                            )
                        }}
                    </p>
                    <dl class="mt-4 grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ monthlyComparison.previousLabel }}
                            </dt>
                            <dd class="mt-1 font-medium text-gray-900 tabular-nums dark:text-white">
                                {{ currencyFormatter(monthlyComparison.previousTotal) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ monthlyComparison.currentLabel }}
                            </dt>
                            <dd class="mt-1 font-medium text-gray-900 tabular-nums dark:text-white">
                                {{ currencyFormatter(monthlyComparison.currentTotal) }}
                            </dd>
                        </div>
                    </dl>
                    <p
                        v-if="missingComparisonRecords(monthlyComparison)"
                        class="mt-3 text-xs text-gray-500 dark:text-gray-400"
                    >
                        {{ missingComparisonRecords(monthlyComparison) }}
                    </p>
                    <template v-if="monthlyComparison.drivers.length">
                        <h3 class="mt-5 text-xs font-medium text-gray-500 dark:text-gray-400">
                            Largest category changes
                        </h3>
                        <ul class="mt-2 divide-y divide-gray-950/5 dark:divide-white/10">
                            <li
                                v-for="driver in monthlyComparison.drivers"
                                :key="driver.categoryId"
                            >
                                <Link
                                    :href="driver.expensesUrl"
                                    class="flex items-center justify-between gap-4 rounded-md py-3 text-sm hover:bg-gray-50 dark:hover:bg-white/5"
                                >
                                    <span class="min-w-0">
                                        <span
                                            class="font-medium wrap-anywhere text-indigo-600 dark:text-indigo-400"
                                            >{{ driver.name }}</span
                                        >
                                        <span
                                            class="mt-1 block text-xs text-gray-500 tabular-nums dark:text-gray-400"
                                        >
                                            {{ currencyFormatter(driver.previousTotal) }} →
                                            {{ currencyFormatter(driver.currentTotal) }}
                                        </span>
                                    </span>
                                    <span
                                        class="shrink-0 font-medium tabular-nums"
                                        :class="spendingChangeClass(driver.difference)"
                                        >{{ signedCurrency(driver.difference) }}</span
                                    >
                                </Link>
                            </li>
                        </ul>
                    </template>
                    <Link
                        :href="monthlyComparison.expensesUrl"
                        class="mt-3 inline-block text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                    >
                        View {{ monthlyComparison.currentLabel }} expenses
                    </Link>
                </div>
                <p v-else class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                    Monthly comparisons will appear after the first completed month.
                </p>
                <div
                    v-if="annualComparison"
                    data-testid="annual-comparison"
                    class="border-t border-gray-950/5 px-5 py-4 dark:border-white/10"
                >
                    <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Same period last year
                    </h3>
                    <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ comparisonLabel(annualComparison) }}
                    </p>
                    <p
                        v-if="annualComparison.percentageChange !== null"
                        class="mt-1 text-xs"
                        :class="spendingChangeClass(annualComparison.difference)"
                    >
                        {{
                            percentageLabel(
                                annualComparison.percentageChange,
                                annualComparison.difference
                            )
                        }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ annualComparison.previousLabel }}:
                        {{ currencyFormatter(annualComparison.previousTotal) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ annualComparison.currentLabel }}:
                        {{ currencyFormatter(annualComparison.currentTotal) }}
                    </p>
                    <p
                        v-if="missingComparisonRecords(annualComparison)"
                        class="mt-2 text-xs text-gray-500 dark:text-gray-400"
                    >
                        {{ missingComparisonRecords(annualComparison) }}
                    </p>
                </div>
            </section>

            <section
                class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Monthly cashflow
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Select a month to view its expenses.
                    </p>
                </div>
                <ul
                    class="grid grid-cols-[auto_minmax(0,1fr)_auto] gap-x-3 divide-y divide-gray-950/5 dark:divide-white/10"
                >
                    <li
                        v-for="month in visibleMonths"
                        :key="month.monthKey"
                        :data-testid="`cashflow-${month.monthKey}`"
                        class="col-span-full grid grid-cols-subgrid px-5 py-3"
                    >
                        <div
                            class="col-span-full flex flex-wrap items-center justify-between gap-x-4 gap-y-1"
                        >
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="month.expensesUrl"
                                    class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                    :aria-label="`View expenses for ${month.label} ${month.monthKey.slice(0, 4)}`"
                                >
                                    {{ month.label }}
                                </Link>
                                <span
                                    v-if="month.isCurrent"
                                    class="rounded bg-indigo-50 px-1.5 py-0.5 text-xs text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300"
                                    >In progress</span
                                >
                                <span
                                    v-else-if="!month.hasExpenses && !month.hasIncome"
                                    class="text-xs text-gray-400 dark:text-gray-500"
                                    >No records</span
                                >
                            </div>
                            <span
                                class="text-right text-xs font-medium tabular-nums"
                                :class="balanceClass(month.net)"
                            >
                                Net
                                {{
                                    month.hasExpenses || month.hasIncome
                                        ? signedCurrency(month.net)
                                        : '—'
                                }}
                            </span>
                        </div>
                        <div
                            class="col-span-full mt-2 grid grid-cols-subgrid items-center gap-y-2 text-xs"
                        >
                            <span class="text-gray-500 dark:text-gray-400">Income</span>
                            <div
                                data-testid="cashflow-bar-track"
                                class="h-1.5 rounded-full bg-gray-100 dark:bg-white/10"
                            >
                                <div
                                    class="h-1.5 rounded-full bg-emerald-400 transition-all duration-300 dark:bg-emerald-500"
                                    :style="{ width: flowWidth(month.income) }"
                                ></div>
                            </div>
                            <span
                                data-testid="income-amount"
                                class="text-right font-medium whitespace-nowrap text-gray-900 tabular-nums dark:text-white"
                                >{{ currencyFormatter(month.income) }}</span
                            >
                            <span class="text-gray-500 dark:text-gray-400">Spent</span>
                            <div
                                data-testid="cashflow-bar-track"
                                class="h-1.5 rounded-full bg-gray-100 dark:bg-white/10"
                            >
                                <div
                                    class="h-1.5 rounded-full bg-indigo-400 transition-all duration-300 dark:bg-indigo-500"
                                    :style="{ width: flowWidth(month.spent) }"
                                ></div>
                            </div>
                            <span
                                data-testid="spent-amount"
                                class="text-right font-medium whitespace-nowrap text-gray-900 tabular-nums dark:text-white"
                                >{{ currencyFormatter(month.spent) }}</span
                            >
                        </div>
                    </li>
                </ul>
            </section>

            <section
                v-if="categoryTotals.length"
                data-testid="category-comparison"
                class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Spending by category
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        All expenses · changes compare the same period last year
                    </p>
                </div>
                <table class="w-full text-left text-sm">
                    <thead
                        class="hidden text-xs text-gray-500 sm:table-header-group dark:text-gray-400"
                    >
                        <tr>
                            <th scope="col" class="px-5 pt-4 pb-2 font-medium">Category / share</th>
                            <th scope="col" class="px-3 pt-4 pb-2 text-right font-medium">Total</th>
                            <th scope="col" class="px-5 pt-4 pb-2 text-right font-medium">
                                Change vs last year
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-950/5 dark:divide-white/10">
                        <tr
                            v-for="category in visibleCategories"
                            :key="category.id ?? 'other'"
                            :data-testid="
                                category.id === null
                                    ? 'other-categories'
                                    : `report-category-${category.id}`
                            "
                            class="grid grid-cols-2 gap-x-3 px-5 py-3 sm:table-row"
                        >
                            <th scope="row" class="col-span-2 pb-2 font-medium sm:px-5 sm:py-3">
                                <Link
                                    v-if="category.expensesUrl"
                                    :href="category.expensesUrl"
                                    class="wrap-anywhere text-indigo-600 hover:underline dark:text-indigo-400"
                                    >{{ category.name }}</Link
                                >
                                <span v-else class="text-gray-900 dark:text-white">{{
                                    category.name
                                }}</span>
                                <span
                                    class="mt-1 block text-xs font-normal text-gray-500 dark:text-gray-400"
                                    >{{ category.share }}% of spending</span
                                >
                            </th>
                            <td
                                class="text-gray-900 tabular-nums sm:px-3 sm:py-3 sm:text-right dark:text-white"
                            >
                                <span
                                    class="mb-1 block text-xs text-gray-500 sm:hidden dark:text-gray-400"
                                    >Total</span
                                >
                                {{ currencyFormatter(category.total) }}
                            </td>
                            <td class="text-right tabular-nums sm:px-5 sm:py-3">
                                <span
                                    class="mb-1 block text-xs text-gray-500 sm:hidden dark:text-gray-400"
                                    >Change vs last year</span
                                >
                                <template v-if="annualComparison?.previousHasExpenses">
                                    <span :class="spendingChangeClass(category.change)">{{
                                        signedCurrency(category.change)
                                    }}</span>
                                    <span
                                        class="mt-1 block text-xs text-gray-500 dark:text-gray-400"
                                        >{{
                                            category.changePercent === null
                                                ? 'No prior records'
                                                : percentageLabel(
                                                      category.changePercent,
                                                      category.change
                                                  )
                                        }}</span
                                    >
                                </template>
                                <span v-else class="text-xs text-gray-500 dark:text-gray-400"
                                    >No prior records</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div
                    v-if="categoryTotals.length > 8"
                    class="border-t border-gray-950/5 px-5 py-3 dark:border-white/10"
                >
                    <button
                        type="button"
                        :aria-expanded="showAllCategories"
                        @click="showAllCategories = !showAllCategories"
                        class="cursor-pointer text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                    >
                        {{
                            showAllCategories
                                ? 'Show top 8 categories'
                                : `Show all ${categoryTotals.length} categories`
                        }}
                    </button>
                </div>
            </section>

            <details
                v-if="biggestExpenses.length"
                :key="fy"
                data-testid="biggest-expenses"
                class="group mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <summary
                    class="cursor-pointer px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white"
                >
                    Biggest expenses
                </summary>
                <ul
                    class="divide-y divide-gray-950/5 border-t border-gray-950/5 dark:divide-white/10 dark:border-white/10"
                >
                    <li v-for="expense in biggestExpenses" :key="expense.id">
                        <Link
                            :href="expense.url"
                            class="flex items-center justify-between gap-4 px-5 py-3.5 transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{ expense.category }}
                                </p>
                                <p
                                    v-if="expense.note"
                                    class="mt-1 text-xs break-words text-gray-500 dark:text-gray-400"
                                >
                                    {{ expense.note }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ expense.date }}
                                </p>
                            </div>
                            <p
                                class="shrink-0 text-sm font-semibold text-gray-900 tabular-nums dark:text-white"
                            >
                                {{ currencyFormatter(expense.amount) }}
                            </p>
                        </Link>
                    </li>
                </ul>
            </details>
        </template>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { FinancialYearOption } from '@/types/app/Models/Income';
import currencyFormatter from '@/utils/currencyFormatter';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface MonthCashflow {
    monthKey: string;
    label: string;
    spent: number;
    income: number;
    net: number;
    isCurrent: boolean;
    isFuture: boolean;
    hasExpenses: boolean;
    hasIncome: boolean;
    expensesUrl: string;
}

interface SpendingComparison {
    currentLabel: string;
    previousLabel: string;
    currentTotal: number;
    previousTotal: number;
    difference: number;
    percentageChange: number | null;
    currentHasExpenses: boolean;
    previousHasExpenses: boolean;
}

interface CategoryTotal {
    id: number | null;
    name: string;
    total: number;
    share: number;
    previousTotal: number;
    change: number;
    changePercent: number | null;
    expensesUrl: string | null;
}

const props = withDefaults(
    defineProps<{
        fy: number;
        fyLabel: string;
        availableFys: FinancialYearOption[];
        empty: boolean;
        period: {
            label: string;
            startDate: string;
            endDate: string;
            isCurrent: boolean;
            completedMonths: number;
            averageLabel: string;
        };
        cashflow?: {
            income: number;
            spent: number;
            net: number;
            netRate: number | null;
            avgMonthlySpend: number | null;
        };
        monthly?: MonthCashflow[];
        monthlyComparison?:
            | (SpendingComparison & {
                  expensesUrl: string;
                  drivers: {
                      categoryId: number;
                      name: string;
                      currentTotal: number;
                      previousTotal: number;
                      difference: number;
                      expensesUrl: string;
                  }[];
              })
            | null;
        annualComparison?: SpendingComparison;
        categoryTotals?: CategoryTotal[];
        biggestExpenses?: {
            id: number;
            date: string;
            category: string;
            note: string | null;
            amount: number;
            url: string;
        }[];
    }>(),
    {
        monthly: () => [],
        categoryTotals: () => [],
        biggestExpenses: () => [],
    }
);

const selectedFy = ref(props.fy);
const showAllCategories = ref(false);

watch(
    () => props.fy,
    (fy) => {
        selectedFy.value = fy;
        showAllCategories.value = false;
    }
);

const switchFy = () => {
    router.get('/reports', { fy: selectedFy.value }, { preserveScroll: true });
};

const visibleMonths = computed(() => props.monthly.filter((month) => !month.isFuture));
const maxFlow = computed(() =>
    Math.max(...visibleMonths.value.map((month) => Math.max(month.spent, month.income)), 1)
);
const flowWidth = (value: number) => `${Math.max(0, (value / maxFlow.value) * 100)}%`;
const signedCurrency = (value: number) =>
    `${value > 0 ? '+' : value < 0 ? '−' : ''}${currencyFormatter(Math.abs(value))}`;
const balanceClass = (value: number) =>
    value === 0
        ? 'text-gray-500 dark:text-gray-400'
        : value > 0
          ? 'text-emerald-600 dark:text-emerald-400'
          : 'text-rose-600 dark:text-rose-400';
const spendingChangeClass = (value: number) => balanceClass(-value);
const percentageLabel = (value: number, difference: number) => {
    if (difference === 0) {
        return 'No change';
    }

    return `${value === 0 ? '<0.1' : Math.abs(value)}% ${difference > 0 ? 'increase' : 'decrease'}`;
};

const comparisonLabel = (comparison: SpendingComparison) => {
    if (!comparison.currentHasExpenses && !comparison.previousHasExpenses) {
        return 'No expenses recorded in either period.';
    }
    if (comparison.difference === 0) {
        return 'Recorded spending did not change.';
    }
    return `Recorded spending ${comparison.difference > 0 ? 'increased' : 'decreased'} by ${currencyFormatter(Math.abs(comparison.difference))}`;
};

const missingComparisonRecords = (comparison: SpendingComparison) => {
    if (comparison.currentHasExpenses === comparison.previousHasExpenses) {
        return null;
    }
    return `No expenses recorded in ${comparison.currentHasExpenses ? comparison.previousLabel : comparison.currentLabel}.`;
};

const visibleCategories = computed<CategoryTotal[]>(() => {
    if (showAllCategories.value || props.categoryTotals.length <= 8) {
        return props.categoryTotals;
    }
    const remaining = props.categoryTotals.slice(8);
    const total = remaining.reduce((sum, category) => sum + category.total, 0);
    const previousTotal = remaining.reduce((sum, category) => sum + category.previousTotal, 0);
    const change = Math.round((total - previousTotal) * 100) / 100;
    return [
        ...props.categoryTotals.slice(0, 8),
        {
            id: null,
            name: 'Other categories',
            total,
            previousTotal,
            change,
            share: props.cashflow?.spent
                ? Math.round((total / props.cashflow.spent) * 1000) / 10
                : 0,
            changePercent:
                previousTotal > 0 ? Math.round((change / previousTotal) * 1000) / 10 : null,
            expensesUrl: null,
        },
    ];
});
</script>
