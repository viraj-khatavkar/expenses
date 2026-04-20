<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <Stat>
                <template #header>{{ fyLabel }}</template>
                {{ currencyFormatter(total) }}
            </Stat>
            <div class="px-4 sm:px-6 lg:px-8">
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
            </div>
        </div>

        <div v-if="incomes.length === 0" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
            No income recorded for {{ fyLabel }}.
        </div>

        <div v-else class="h-full overflow-y-auto" aria-label="Directory">
            <div v-for="group in incomes" :key="group.month" class="relative">
                <div
                    class="flex justify-between border-y border-t-gray-100 border-b-gray-200 bg-gray-50 px-3 py-1.5 text-sm/6 font-semibold text-gray-500 dark:border-t-white/5 dark:border-b-white/10 dark:bg-gray-900 dark:text-white dark:before:pointer-events-none dark:before:absolute dark:before:inset-0 dark:before:bg-white/5"
                >
                    <h3 class="relative">{{ group.month }}</h3>
                    <span class="relative">{{ currencyFormatter(group.total) }}</span>
                </div>
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/5">
                    <li v-for="income in group.incomes" :key="income.id" class="px-3 py-5">
                        <Link :href="`/income/${income.id}/edit`">
                            <div class="flex justify-between tracking-wide">
                                <div>
                                    <p class="text-sm/6 font-semibold text-gray-800 dark:text-white">
                                        {{ income.source.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(income.date) }}
                                    </p>
                                </div>
                                <p
                                    class="lg:text-md text-sm/6 font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ currencyFormatter(income.amount) }}
                                </p>
                            </div>
                        </Link>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import Stat from '@/Components/Stat.vue';
import { FinancialYearOption, IncomeMonthGroup } from '@/types/app/Models/Income';
import currencyFormatter from '@/utils/currencyFormatter';
import { Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { ref } from 'vue';

const props = defineProps<{
    fy: number;
    fyLabel: string;
    availableFys: FinancialYearOption[];
    incomes: IncomeMonthGroup[];
    total: number;
}>();

const selectedFy = ref(props.fy);

const switchFy = () => {
    router.get('/income', { fy: selectedFy.value }, { preserveScroll: true });
};

const formatDate = (date: string) => dayjs(date).format('D MMM');
</script>
