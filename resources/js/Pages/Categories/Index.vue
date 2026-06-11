<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Categories" />

        <PageHeader title="Categories" :subtitle="`Spending per category in ${fyLabel}`">
            <Link href="/categories/create">
                <PrimaryButton>Add Category</PrimaryButton>
            </Link>
        </PageHeader>

        <div
            v-if="categories.length === 0"
            class="rounded-xl bg-white px-6 py-16 text-center text-sm text-gray-400 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-500 dark:ring-white/10"
        >
            No categories yet.
        </div>

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Link
                v-for="category in categories"
                :key="category.id"
                :href="`/categories/${category.id}/edit`"
                class="rounded-xl bg-white px-5 py-4 shadow-sm ring-1 ring-gray-950/5 transition-colors hover:bg-gray-50 dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10"
            >
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ category.name }}
                    </p>
                    <span
                        class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white"
                    >
                        {{ compactCurrencyFormatter(category.expenses_total ?? 0) }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                    {{ category.expenses_count }}
                    {{ category.expenses_count === 1 ? 'expense' : 'expenses' }} this year
                </p>
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { Category } from '@/types/app/Models/Category';
import compactCurrencyFormatter from '@/utils/compactCurrencyFormatter';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';

defineProps<{
    fyLabel: string;
    categories: Category[];
}>();
</script>
