<template>
    <div>
        <!-- Hero — FY Total -->
        <div
            id="yearly-stat"
            class="rounded-xl bg-gray-50 px-6 py-10 shadow-sm ring-1 ring-gray-950/5 sm:px-8 dark:bg-white/5 dark:ring-white/10"
        >
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ fyLabel }}</p>
            <p class="mt-3 text-4xl font-bold tracking-wide text-gray-900 sm:text-5xl dark:text-white">
                {{ currencyFormatter(thisYearTotal) }}
            </p>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-5">
            <!-- Monthly — expandable rows -->
            <div
                class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 lg:col-span-3 dark:bg-white/5 dark:ring-white/10"
            >
                <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Monthly</h2>
                </div>
                <!-- Month grid -->
                <div
                    class="grid grid-cols-1 gap-px bg-gray-950/5 sm:grid-cols-3 dark:bg-white/10"
                >
                    <div
                        v-for="monthlyStat in thisYearMonthlyTotals"
                        :key="monthlyStat.month"
                        :id="monthlyStat.month"
                        :class="
                            expandedMonth === monthlyStat.month
                                ? 'bg-gray-50 dark:bg-white/[0.04]'
                                : 'bg-white dark:bg-white/[0.02]'
                        "
                    >
                        <button
                            class="w-full px-4 py-4 text-left transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.04]"
                            @click="toggleMonth(monthlyStat.month)"
                        >
                            <p
                                class="text-xs font-medium text-gray-400 dark:text-gray-500"
                            >
                                {{ monthlyStat.month }}
                            </p>
                            <p
                                class="mt-1 text-base font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ compactCurrencyFormatter(monthlyStat.total) }}
                            </p>
                        </button>
                        <!-- Expanded category breakdown -->
                        <div
                            v-if="expandedMonth === monthlyStat.month"
                            class="border-t border-gray-950/5 px-4 pb-3 dark:border-white/10"
                        >
                            <div
                                v-for="cat in monthlyStat.categories"
                                :key="cat.category"
                                class="flex items-center justify-between py-2"
                            >
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ cat.category }}
                                </span>
                                <span
                                    class="text-xs tabular-nums text-gray-500 dark:text-gray-400"
                                >
                                    {{ compactCurrencyFormatter(cat.total) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="thisYearMonthlyTotals.length === 0"
                    class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500"
                >
                    No expenses recorded yet.
                </div>
            </div>

            <!-- Categories — percentage bars -->
            <div
                class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 lg:sticky lg:top-8 lg:col-span-2 lg:self-start dark:bg-white/5 dark:ring-white/10"
            >
                <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Categories</h2>
                </div>
                <ul class="divide-y divide-gray-950/5 dark:divide-white/10">
                    <li
                        v-for="cat in categoryTotals"
                        :key="cat.category"
                        class="px-5 py-4"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ cat.category }}
                            </span>
                            <span
                                class="text-base font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ compactCurrencyFormatter(cat.total) }}
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center gap-3">
                            <div
                                class="h-1.5 flex-1 rounded-full bg-gray-100 dark:bg-white/10"
                            >
                                <div
                                    class="h-1.5 rounded-full bg-emerald-500 transition-all duration-300 dark:bg-emerald-400"
                                    :style="{ width: cat.percentage + '%' }"
                                ></div>
                            </div>
                            <span
                                class="w-10 text-right text-xs tabular-nums text-gray-400 dark:text-gray-500"
                            >
                                {{ cat.percentage.toFixed(0) }}%
                            </span>
                        </div>
                    </li>
                </ul>
                <div
                    v-if="categoryTotals.length === 0"
                    class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500"
                >
                    No expenses recorded yet.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import compactCurrencyFormatter from '@/utils/compactCurrencyFormatter';
import currencyFormatter from '@/utils/currencyFormatter';
import { computed, ref } from 'vue';

const props = defineProps<{
    fyLabel: string;
    thisYearTotal: number;
    thisYearMonthlyTotals: {
        month: string;
        total: number;
        categories: { category: string; total: number }[];
    }[];
}>();

const expandedMonth = ref<string | null>(null);

function toggleMonth(month: string) {
    expandedMonth.value = expandedMonth.value === month ? null : month;
}

const categoryTotals = computed(() => {
    const map = new Map<string, number>();
    for (const month of props.thisYearMonthlyTotals) {
        for (const cat of month.categories) {
            map.set(cat.category, (map.get(cat.category) || 0) + cat.total);
        }
    }
    return Array.from(map.entries())
        .map(([category, total]) => ({
            category,
            total,
            percentage: props.thisYearTotal > 0 ? (total / props.thisYearTotal) * 100 : 0,
        }))
        .sort((a, b) => b.total - a.total);
});
</script>
