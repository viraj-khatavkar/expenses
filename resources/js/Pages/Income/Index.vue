<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Income" />

        <PageHeader title="Income">
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

        <Stat>
            <template #header>Received in {{ fyLabel }}</template>
            {{ currencyFormatter(total) }}
        </Stat>

        <div
            v-if="sourceTotals.length > 0"
            class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">By Source</h2>
            </div>
            <ul class="divide-y divide-gray-950/5 dark:divide-white/10">
                <li
                    v-for="st in sourceTotals"
                    :key="st.source"
                    class="px-5 py-4"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ st.source }}
                        </span>
                        <span
                            class="text-base font-semibold tabular-nums text-gray-900 dark:text-white"
                        >
                            {{ compactCurrencyFormatter(st.total) }}
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-center gap-3">
                        <div
                            class="h-1.5 flex-1 rounded-full bg-gray-100 dark:bg-white/10"
                        >
                            <div
                                class="h-1.5 rounded-full bg-emerald-500 transition-all duration-300 dark:bg-emerald-400"
                                :style="{ width: st.percentage + '%' }"
                            ></div>
                        </div>
                        <span
                            class="w-10 text-right text-xs tabular-nums text-gray-400 dark:text-gray-500"
                        >
                            {{ st.percentage.toFixed(0) }}%
                        </span>
                    </div>
                </li>
            </ul>
        </div>

        <div
            v-if="incomes.length === 0"
            class="mt-6 rounded-xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <p class="text-sm text-gray-400 dark:text-gray-500">
                No income recorded for {{ fyLabel }}.
            </p>
        </div>

        <div
            v-else
            class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <div v-for="group in incomes" :key="group.month">
                <div
                    class="flex items-center justify-between border-y border-t-gray-100 border-b-gray-200 bg-gray-50 px-5 py-1.5 dark:border-t-white/5 dark:border-b-white/10 dark:bg-gray-900"
                >
                    <h3 class="text-sm/6 font-semibold text-gray-500 dark:text-white">
                        {{ group.month }}
                    </h3>
                    <span
                        class="text-sm/6 font-semibold tabular-nums text-gray-500 dark:text-gray-400"
                    >
                        {{ currencyFormatter(group.total) }}
                    </span>
                </div>
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/5">
                    <li v-for="income in group.incomes" :key="income.id">
                        <Link
                            :href="`/income/${income.id}/edit`"
                            :data-testid="`income-${income.id}`"
                            class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="min-w-0">
                                <p class="text-sm/6 font-semibold text-gray-800 dark:text-white">
                                    {{ income.source.name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ formatDate(income.date) }}
                                </p>
                            </div>
                            <p
                                class="shrink-0 text-sm/6 font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ currencyFormatter(income.amount) }}
                            </p>
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import Stat from '@/Components/Stat.vue';
import { FinancialYearOption, IncomeMonthGroup, IncomeSourceTotal } from '@/types/app/Models/Income';
import compactCurrencyFormatter from '@/utils/compactCurrencyFormatter';
import currencyFormatter from '@/utils/currencyFormatter';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { ref } from 'vue';

const props = defineProps<{
    fy: number;
    fyLabel: string;
    availableFys: FinancialYearOption[];
    incomes: IncomeMonthGroup[];
    total: number;
    sourceTotals: IncomeSourceTotal[];
}>();

const selectedFy = ref(props.fy);

const switchFy = () => {
    router.get('/income', { fy: selectedFy.value }, { preserveScroll: true });
};

const formatDate = (date: string) => dayjs(date).format('D MMM');
</script>
