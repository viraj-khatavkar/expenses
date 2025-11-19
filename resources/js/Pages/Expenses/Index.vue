<template>
    <div>
        <Stat>
            <template #header>This Month</template>
            {{ currencyFormatter(total) }}
        </Stat>
        <div class="h-full overflow-y-auto" aria-label="Directory">
            <div v-for="date in Object.keys(expenses)" :key="date" class="relative">
                <div
                    class="border-y border-t-gray-100 border-b-gray-200 bg-gray-50 px-3 py-1.5 text-sm/6 font-semibold text-gray-500 dark:border-t-white/5 dark:border-b-white/10 dark:bg-gray-900 dark:text-white dark:before:pointer-events-none dark:before:absolute dark:before:inset-0 dark:before:bg-white/5"
                >
                    <h3 class="relative">{{ date }}</h3>
                </div>
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/5">
                    <li v-for="expense in expenses[date]" :key="expense.id" class="px-3 py-5">
                        <Link :href="`/expenses/${expense.id}/edit`">
                            <div class="flex justify-between tracking-wide">
                                <p class="text-sm/6 font-semibold text-gray-800 dark:text-white">
                                    {{ expense.category.name }}
                                </p>
                                <p
                                    class="lg:text-md text-sm/6 font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ currencyFormatter(expense.amount) }}
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
import { Expense } from '@/types/app/Models/Expense';
import currencyFormatter from '@/utils/currencyFormatter';
import { Link } from '@inertiajs/vue3';

defineProps<{
    expenses: Record<string, Expense[]>;
    total: Number;
}>();
</script>
