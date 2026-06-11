<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Expenses" />

        <PageHeader title="Expenses">
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    data-test="prev-month"
                    aria-label="Previous month"
                    class="cursor-pointer rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    @click="goToMonth(-1)"
                >
                    <ChevronLeftIcon class="size-5" aria-hidden="true" />
                </button>
                <span
                    class="w-32 text-center text-sm font-semibold text-gray-900 dark:text-white"
                >
                    {{ monthLabel }}
                </span>
                <button
                    type="button"
                    data-test="next-month"
                    aria-label="Next month"
                    :disabled="isCurrentMonth"
                    class="cursor-pointer rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent dark:hover:bg-white/10 dark:hover:text-gray-300"
                    @click="goToMonth(1)"
                >
                    <ChevronRightIcon class="size-5" aria-hidden="true" />
                </button>
            </div>
        </PageHeader>

        <Stat>
            <template #header>Spent in {{ monthLabel }}</template>
            {{ currencyFormatter(total) }}
        </Stat>

        <div
            v-if="expenseGroups.length === 0"
            class="mt-6 rounded-xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <p class="text-sm text-gray-400 dark:text-gray-500">
                No expenses in {{ monthLabel }}.
            </p>
            <Link v-if="isCurrentMonth" href="/expenses/create" class="mt-5 inline-block">
                <PrimaryButton>Add an expense</PrimaryButton>
            </Link>
        </div>

        <div
            v-else
            class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <div v-for="group in expenseGroups" :key="group.label">
                <div
                    class="flex items-center justify-between border-y border-t-gray-100 border-b-gray-200 bg-gray-50 px-5 py-1.5 dark:border-t-white/5 dark:border-b-white/10 dark:bg-gray-900"
                >
                    <h3 class="text-sm/6 font-semibold text-gray-500 dark:text-white">
                        {{ group.label }}
                    </h3>
                    <span
                        class="text-sm/6 font-semibold tabular-nums text-gray-500 dark:text-gray-400"
                    >
                        {{ currencyFormatter(group.total) }}
                    </span>
                </div>
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/5">
                    <li v-for="expense in group.expenses" :key="expense.id">
                        <Link
                            :href="`/expenses/${expense.id}/edit`"
                            class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="min-w-0">
                                <p
                                    class="text-sm/6 font-semibold text-gray-800 dark:text-white"
                                >
                                    {{ expense.category.name }}
                                </p>
                                <p
                                    v-if="expense.note"
                                    class="truncate text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ expense.note }}
                                </p>
                            </div>
                            <p
                                class="shrink-0 text-sm/6 font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ currencyFormatter(expense.amount) }}
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
import PrimaryButton from '@/Components/Button/PrimaryButton.vue';
import Stat from '@/Components/Stat.vue';
import { ExpenseDayGroup } from '@/types/app/Models/Expense';
import currencyFormatter from '@/utils/currencyFormatter';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';
import { Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const props = defineProps<{
    month: string;
    monthLabel: string;
    isCurrentMonth: boolean;
    expenseGroups: ExpenseDayGroup[];
    total: number;
}>();

const goToMonth = (delta: number) => {
    const target = dayjs(`${props.month}-01`).add(delta, 'month').format('YYYY-MM');
    const params = target === dayjs().format('YYYY-MM') ? {} : { month: target };

    router.get('/expenses', params, { preserveScroll: true });
};
</script>
