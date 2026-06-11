<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Sources" />

        <PageHeader title="Sources" :subtitle="`Income per source in ${fyLabel}`">
            <Link href="/sources/create">
                <PrimaryButton>Add Source</PrimaryButton>
            </Link>
        </PageHeader>

        <div
            v-if="sources.length === 0"
            class="rounded-xl bg-white px-6 py-16 text-center text-sm text-gray-400 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-500 dark:ring-white/10"
        >
            No sources yet.
        </div>

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Link
                v-for="source in sources"
                :key="source.id"
                :href="`/sources/${source.id}/edit`"
                class="rounded-xl bg-white px-5 py-4 shadow-sm ring-1 ring-gray-950/5 transition-colors hover:bg-gray-50 dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10"
            >
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ source.name }}
                    </p>
                    <span
                        class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white"
                    >
                        {{ compactCurrencyFormatter(source.incomes_total ?? 0) }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                    {{ source.incomes_count }}
                    {{ source.incomes_count === 1 ? 'payment' : 'payments' }} this year
                </p>
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { Source } from '@/types/app/Models/Source';
import compactCurrencyFormatter from '@/utils/compactCurrencyFormatter';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';

defineProps<{
    fyLabel: string;
    sources: Source[];
}>();
</script>
