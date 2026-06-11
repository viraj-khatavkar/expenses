<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Reports" />

        <PageHeader title="Reports" :subtitle="`Cashflow and trends for ${fyLabel}`">
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

        <!-- Empty state -->
        <div
            v-if="empty"
            class="rounded-xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <p class="text-sm text-gray-400 dark:text-gray-500">
                Nothing recorded in {{ fyLabel }} yet.
            </p>
        </div>

        <template v-else>
            <!-- Cashflow summary -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div
                    class="rounded-xl bg-gray-50 px-5 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Income</p>
                    <p
                        class="mt-1 text-2xl font-bold tracking-wide text-gray-900 dark:text-white"
                    >
                        {{ compactCurrencyFormatter(cashflow!.income) }}
                    </p>
                </div>
                <div
                    class="rounded-xl bg-gray-50 px-5 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Spent</p>
                    <p
                        class="mt-1 text-2xl font-bold tracking-wide text-gray-900 dark:text-white"
                    >
                        {{ compactCurrencyFormatter(cashflow!.spent) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        {{ compactCurrencyFormatter(cashflow!.avgMonthlySpend) }}/mo average
                    </p>
                </div>
                <div
                    class="rounded-xl bg-gray-50 px-5 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">
                        {{ cashflow!.saved >= 0 ? 'Saved' : 'Overspent' }}
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold tracking-wide"
                        :class="
                            cashflow!.saved >= 0
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-rose-500 dark:text-rose-400'
                        "
                    >
                        {{ compactCurrencyFormatter(Math.abs(cashflow!.saved)) }}
                    </p>
                    <p
                        v-if="cashflow!.savingsRate !== null"
                        class="mt-1 text-xs text-gray-400 dark:text-gray-500"
                    >
                        {{ cashflow!.savingsRate }}% of income
                    </p>
                </div>
            </div>

            <!-- Monthly cashflow -->
            <div
                class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-950/5 px-5 py-4 dark:border-white/10"
                >
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Monthly cashflow
                    </h2>
                    <div
                        class="flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500"
                    >
                        <span class="flex items-center gap-1.5">
                            <span class="size-2 rounded-full bg-emerald-400"></span>
                            Income
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="size-2 rounded-full bg-indigo-400"></span>
                            Spent
                        </span>
                    </div>
                </div>
                <ul class="divide-y divide-gray-950/5 dark:divide-white/10">
                    <li
                        v-for="month in visibleMonths"
                        :key="month.monthKey"
                        :data-testid="`cashflow-${month.monthKey}`"
                        class="px-5 py-3"
                    >
                        <div class="flex items-center gap-4">
                            <span
                                class="w-8 shrink-0 text-xs font-medium"
                                :class="
                                    month.isCurrent
                                        ? 'font-semibold text-gray-900 dark:text-white'
                                        : 'text-gray-400 dark:text-gray-500'
                                "
                            >
                                {{ month.label }}
                            </span>
                            <div class="flex-1 space-y-1.5">
                                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-white/10">
                                    <div
                                        class="h-1.5 rounded-full bg-emerald-400 transition-all duration-300 dark:bg-emerald-500"
                                        :style="{ width: flowWidth(month.income) }"
                                    ></div>
                                </div>
                                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-white/10">
                                    <div
                                        class="h-1.5 rounded-full bg-indigo-400 transition-all duration-300 dark:bg-indigo-500"
                                        :style="{ width: flowWidth(month.spent) }"
                                    ></div>
                                </div>
                            </div>
                            <span
                                class="w-16 shrink-0 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ compactCurrencyFormatter(month.spent) }}
                            </span>
                            <span
                                class="w-16 shrink-0 text-right text-xs font-medium tabular-nums"
                                :class="netClass(month)"
                            >
                                {{ netLabel(month) }}
                            </span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Category trends -->
            <div
                v-if="categoryTrends!.length > 0"
                class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-950/5 px-5 py-4 dark:border-white/10"
                >
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Category trends
                    </h2>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Apr → Mar</span>
                </div>
                <ul class="divide-y divide-gray-950/5 dark:divide-white/10">
                    <li v-for="trend in categoryTrends" :key="trend.name" class="px-5 py-4">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ trend.name }}
                                <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ trend.share }}%
                                </span>
                            </p>
                            <p
                                class="shrink-0 text-sm font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ compactCurrencyFormatter(trend.total) }}
                            </p>
                        </div>
                        <div class="mt-2 flex items-end gap-4">
                            <div class="flex h-9 flex-1 items-end gap-1">
                                <div
                                    v-for="(value, index) in trend.monthly"
                                    :key="index"
                                    class="flex-1 rounded-sm transition-all duration-300"
                                    :class="
                                        value > 0
                                            ? index === currentMonthIndex
                                                ? 'bg-indigo-500 dark:bg-indigo-400'
                                                : 'bg-indigo-300 dark:bg-indigo-500/50'
                                            : 'bg-gray-100 dark:bg-white/10'
                                    "
                                    :style="{ height: trendBarHeight(value, trend) }"
                                    :title="`${monthLabels[index]}: ${compactCurrencyFormatter(value)}`"
                                ></div>
                            </div>
                            <p class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                                {{ compactCurrencyFormatter(trend.avgPerMonth) }}/mo
                            </p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Biggest expenses -->
            <div
                v-if="biggestExpenses!.length > 0"
                class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Biggest expenses
                    </h2>
                </div>
                <ul class="divide-y divide-gray-950/5 dark:divide-white/10">
                    <li v-for="expense in biggestExpenses" :key="expense.id">
                        <Link
                            :href="`/expenses/${expense.id}/edit`"
                            class="flex items-center justify-between gap-4 px-5 py-3.5 transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                    {{ expense.category }}
                                    <span
                                        v-if="expense.note"
                                        class="ml-1 font-normal text-gray-400 dark:text-gray-500"
                                    >
                                        · {{ expense.note }}
                                    </span>
                                </p>
                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                    {{ expense.date }}
                                </p>
                            </div>
                            <p
                                class="shrink-0 text-sm font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ currencyFormatter(expense.amount) }}
                            </p>
                        </Link>
                    </li>
                </ul>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { FinancialYearOption } from '@/types/app/Models/Income';
import compactCurrencyFormatter from '@/utils/compactCurrencyFormatter';
import currencyFormatter from '@/utils/currencyFormatter';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface MonthCashflow {
    monthKey: string;
    label: string;
    spent: number;
    income: number;
    net: number;
    isCurrent: boolean;
    isFuture: boolean;
}

interface CategoryTrend {
    name: string;
    total: number;
    count: number;
    share: number;
    avgPerMonth: number;
    monthly: number[];
}

interface BiggestExpense {
    id: number;
    date: string;
    category: string;
    note: string | null;
    amount: number;
}

const props = defineProps<{
    fy: number;
    fyLabel: string;
    availableFys: FinancialYearOption[];
    empty: boolean;
    cashflow?: {
        income: number;
        spent: number;
        saved: number;
        savingsRate: number | null;
        avgMonthlySpend: number;
    };
    monthly?: MonthCashflow[];
    categoryTrends?: CategoryTrend[];
    biggestExpenses?: BiggestExpense[];
}>();

const selectedFy = ref(props.fy);

const switchFy = () => {
    router.get('/reports', { fy: selectedFy.value }, { preserveScroll: true });
};

const visibleMonths = computed(() =>
    (props.monthly ?? []).filter(
        (month) => !month.isFuture || month.spent > 0 || month.income > 0,
    ),
);

const monthLabels = computed(() => (props.monthly ?? []).map((month) => month.label));

const currentMonthIndex = computed(() =>
    (props.monthly ?? []).findIndex((month) => month.isCurrent),
);

const maxFlow = computed(() =>
    Math.max(...visibleMonths.value.map((month) => Math.max(month.spent, month.income)), 1),
);

const flowWidth = (value: number) => {
    if (value <= 0) {
        return '0%';
    }

    return Math.max(2, Math.round((value / maxFlow.value) * 100)) + '%';
};

const trendBarHeight = (value: number, trend: CategoryTrend) => {
    if (value <= 0) {
        return '2px';
    }

    const max = Math.max(...trend.monthly, 1);

    return Math.max(4, Math.round((value / max) * 36)) + 'px';
};

const netClass = (month: MonthCashflow) => {
    if (month.spent === 0 && month.income === 0) {
        return 'text-gray-300 dark:text-gray-600';
    }

    return month.net >= 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-rose-500 dark:text-rose-400';
};

const netLabel = (month: MonthCashflow) => {
    if (month.spent === 0 && month.income === 0) {
        return '—';
    }

    const sign = month.net >= 0 ? '+' : '−';

    return sign + compactCurrencyFormatter(Math.abs(month.net));
};
</script>
