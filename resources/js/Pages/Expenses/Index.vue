<template>
    <div>
        <div class="bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-1 bg-gray-900/5 dark:bg-white/10">
                    <div class="bg-white px-4 py-6 sm:px-6 lg:px-8 dark:bg-gray-900">
                        <p class="text-sm/6 font-medium text-gray-500 dark:text-gray-400">
                            This Month:
                        </p>
                        <p class="mt-2 flex items-baseline gap-x-2">
                            <span
                                class="text-4xl font-semibold tracking-wider text-gray-900 dark:text-white"
                            >
                                {{ currencyFormatter(total) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5 flex justify-end">
            <Link href="/expenses/create">
                <PrimaryButton>Add Expense</PrimaryButton>
            </Link>
        </div>

        <nav class="h-full overflow-y-auto" aria-label="Directory">
            <div v-for="date in Object.keys(expenses)" :key="date" class="relative">
                <div
                    class="sticky top-0 z-10 border-y border-t-gray-100 border-b-gray-200 bg-gray-50 px-3 py-1.5 text-sm/6 font-semibold text-gray-500 dark:border-t-white/5 dark:border-b-white/10 dark:bg-gray-900 dark:text-white dark:before:pointer-events-none dark:before:absolute dark:before:inset-0 dark:before:bg-white/5"
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
        </nav>
    </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import currencyFormatter from '../../utils/currencyFormatter.js';

defineProps({
    expenses: {
        type: Object,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
});
</script>
