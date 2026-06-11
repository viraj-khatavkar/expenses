<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Explore" />

        <PageHeader title="Explore" subtitle="Add up any combination of categories.">
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

        <Stat class="mb-6">
            <template #header>Selected total · {{ fyLabel }}</template>
            {{ currencyFormatter(computedTotal) }}
        </Stat>

        <div v-if="categoryTotals.length === 0" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
            No expenses recorded for {{ fyLabel }}.
        </div>

        <div
            v-else
            class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <div class="border-b border-gray-950/5 px-5 py-4 dark:border-white/10">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-lg bg-gray-100 p-0.5 dark:bg-white/10">
                            <button
                                type="button"
                                @click="setMode('include')"
                                :class="[
                                    'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                                    mode === 'include'
                                        ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white'
                                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                                ]"
                            >
                                Include
                            </button>
                            <button
                                type="button"
                                @click="setMode('exclude')"
                                :class="[
                                    'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                                    mode === 'exclude'
                                        ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white'
                                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                                ]"
                            >
                                Exclude
                            </button>
                        </span>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ selectionSummary }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="selectAll"
                            class="rounded-md bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-600 ring-1 ring-gray-300 hover:bg-gray-100 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10"
                        >
                            All
                        </button>
                        <button
                            type="button"
                            @click="selectNone"
                            class="rounded-md bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-600 ring-1 ring-gray-300 hover:bg-gray-100 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10"
                        >
                            None
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 px-5 py-5">
                <button
                    v-for="cat in categoryTotals"
                    :key="cat.id"
                    type="button"
                    @click="toggle(cat.id)"
                    :data-testid="`category-${cat.id}`"
                    :class="[
                        'inline-flex items-center gap-x-2 rounded-full px-3.5 py-2 text-sm font-medium transition-colors',
                        selected.has(cat.id)
                            ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-700/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/30'
                            : 'bg-gray-50 text-gray-500 ring-1 ring-gray-200 hover:bg-gray-100 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10 dark:hover:bg-white/10',
                    ]"
                >
                    {{ cat.name }}
                    <span
                        :class="[
                            'text-xs tabular-nums',
                            selected.has(cat.id)
                                ? 'text-indigo-500 dark:text-indigo-400'
                                : 'text-gray-400 dark:text-gray-500',
                        ]"
                    >
                        {{ compactCurrencyFormatter(cat.total) }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import Stat from '@/Components/Stat.vue';
import { FinancialYearOption } from '@/types/app/Models/Income';
import compactCurrencyFormatter from '@/utils/compactCurrencyFormatter';
import currencyFormatter from '@/utils/currencyFormatter';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface CategoryTotal {
    id: number;
    name: string;
    total: number;
}

const props = defineProps<{
    fy: number;
    fyLabel: string;
    availableFys: FinancialYearOption[];
    categoryTotals: CategoryTotal[];
    grandTotal: number;
}>();

const selectedFy = ref(props.fy);
const mode = ref<'include' | 'exclude'>('include');
const selected = ref(new Set(props.categoryTotals.map((c) => c.id)));

watch(
    () => props.fy,
    () => {
        selectedFy.value = props.fy;
        mode.value = 'include';
        selected.value = new Set(props.categoryTotals.map((c) => c.id));
    },
);

const switchFy = () => {
    router.get('/explore', { fy: selectedFy.value }, { preserveScroll: true });
};

function setMode(newMode: 'include' | 'exclude') {
    if (mode.value === newMode) return;

    const allIds = props.categoryTotals.map((c) => c.id);
    const inverted = new Set(allIds.filter((id) => !selected.value.has(id)));
    selected.value = inverted;
    mode.value = newMode;
}

function toggle(id: number) {
    const next = new Set(selected.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    selected.value = next;
}

function selectAll() {
    selected.value = new Set(props.categoryTotals.map((c) => c.id));
}

function selectNone() {
    selected.value = new Set();
}

const selectedTotal = computed(() =>
    props.categoryTotals
        .filter((c) => selected.value.has(c.id))
        .reduce((sum, c) => sum + c.total, 0),
);

const computedTotal = computed(() =>
    mode.value === 'include' ? selectedTotal.value : props.grandTotal - selectedTotal.value,
);

const includedCount = computed(() =>
    mode.value === 'include'
        ? selected.value.size
        : props.categoryTotals.length - selected.value.size,
);

const selectionSummary = computed(() => {
    const total = props.categoryTotals.length;
    const included = includedCount.value;
    if (included === total) return 'All categories';
    if (included === 0) return 'No categories';
    return `${included} of ${total} categories`;
});
</script>
