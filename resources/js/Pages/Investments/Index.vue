<template>
    <div class="mx-auto max-w-5xl">
        <Head title="Investments" />
        <PageHeader title="Investments">
            <PrimaryButton class="inline-flex items-center gap-1.5" @click="openEditor(null)">
                <PlusIcon class="size-4" aria-hidden="true" /> Add transaction
            </PrimaryButton>
        </PageHeader>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.7fr)_minmax(16rem,1fr)]">
            <div class="contents min-w-0 xl:block xl:space-y-5">
                <section
                    aria-label="Monthly investment summary"
                    class="order-1 overflow-hidden rounded-xl bg-gray-50 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5"
                >
                    <div class="px-5 pt-5 sm:px-6">
                        <nav aria-label="Select month" class="flex items-center gap-2">
                            <Link
                                v-if="selected.period > '1000-01'"
                                :href="monthUrl(-1)"
                                aria-label="Previous month"
                                class="rounded-md p-1.5 text-gray-500 hover:bg-white hover:text-indigo-600"
                            >
                                <ChevronLeftIcon class="size-4" />
                            </Link>
                            <input
                                aria-label="Investment month"
                                type="month"
                                :value="selected.period"
                                min="1000-01"
                                max="9999-12"
                                @change="changeMonth"
                                class="min-w-0 rounded bg-transparent px-1 py-1 text-sm font-semibold text-gray-800 focus:outline-indigo-600 dark:text-white"
                            />
                            <Link
                                v-if="selected.period < '9999-12'"
                                :href="monthUrl(1)"
                                aria-label="Next month"
                                class="rounded-md p-1.5 text-gray-500 hover:bg-white hover:text-indigo-600"
                            >
                                <ChevronRightIcon class="size-4" />
                            </Link>
                        </nav>
                        <dl class="mt-5">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                Net invested
                            </dt>
                            <dd
                                data-test="month-net"
                                class="mt-1 text-4xl font-bold tracking-tight break-words text-gray-900 tabular-nums dark:text-white"
                            >
                                {{ rupees(selected.netPaise) }}
                            </dd>
                        </dl>
                        <dl class="mt-5 flex flex-wrap gap-x-10 gap-y-3 pb-5">
                            <div>
                                <dt
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500"
                                >
                                    <ArrowDownLeftIcon class="size-3.5 text-emerald-600" /> Money in
                                </dt>
                                <dd
                                    class="mt-1 text-base font-semibold text-gray-900 tabular-nums dark:text-white"
                                >
                                    {{ rupees(selected.depositedPaise) }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500"
                                >
                                    <ArrowUpRightIcon class="size-3.5 text-amber-700" /> Redeemed
                                </dt>
                                <dd
                                    class="mt-1 text-base font-semibold text-gray-900 tabular-nums dark:text-white"
                                >
                                    {{ rupees(selected.redeemedPaise) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div class="border-t border-gray-200 px-5 py-3 sm:px-6 dark:border-white/10">
                        <form v-if="editingTarget" class="space-y-3" @submit.prevent="saveTarget">
                            <TextInput
                                v-model="targetForm.planned"
                                label="Monthly target (₹)"
                                name="planned"
                                type="number"
                                step="0.01"
                                min="0"
                                max="9999999999.99"
                                :error="targetForm.errors.planned"
                            />
                            <div class="flex gap-3">
                                <PrimaryButton type="submit" :disabled="targetForm.processing"
                                    >Save target</PrimaryButton
                                >
                                <button
                                    type="button"
                                    class="cursor-pointer px-2 text-sm text-gray-600"
                                    @click="editingTarget = false"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                        <div
                            v-else
                            class="flex flex-wrap items-center justify-between gap-2 text-sm"
                        >
                            <span class="text-gray-600 dark:text-gray-300"
                                >Monthly target
                                <strong
                                    v-if="selected.targetPaise !== null"
                                    class="ml-1 font-semibold text-gray-900 dark:text-white"
                                    >{{ rupees(selected.targetPaise) }}</strong
                                ></span
                            >
                            <button
                                type="button"
                                class="cursor-pointer font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                @click="editTarget"
                            >
                                {{ selected.targetPaise === null ? 'Set target' : 'Edit target' }}
                            </button>
                        </div>
                        <div
                            v-if="
                                !editingTarget &&
                                selected.targetPaise !== null &&
                                selected.targetPaise > 0
                            "
                            class="mt-2 flex items-center gap-3"
                        >
                            <progress
                                aria-label="Monthly target progress"
                                :value="Math.max(0, selected.netPaise)"
                                :max="selected.targetPaise"
                                class="h-1.5 min-w-8 flex-1 accent-indigo-600"
                            />
                            <span class="text-xs text-gray-600 dark:text-gray-300">{{
                                targetProgress
                            }}</span>
                        </div>
                    </div>
                </section>

                <section
                    aria-labelledby="transaction-heading"
                    class="order-3 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5"
                >
                    <h2
                        id="transaction-heading"
                        class="border-b border-gray-100 px-5 py-4 text-sm font-semibold text-gray-900 dark:border-white/10 dark:text-white"
                    >
                        Transactions
                    </h2>
                    <p v-if="!transactions.length" class="px-5 py-8 text-sm text-gray-500">
                        No transactions in {{ monthName }}.
                    </p>
                    <ul v-else class="divide-y divide-gray-100 dark:divide-white/10">
                        <li v-for="entry in transactions" :key="entry.id">
                            <button
                                type="button"
                                :data-test="'transaction-' + entry.id"
                                class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-white/5"
                                @click="openEditor(entry)"
                            >
                                <span
                                    class="grid size-8 shrink-0 place-items-center rounded-full"
                                    :class="
                                        entry.direction === 'deposit'
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-amber-50 text-amber-700'
                                    "
                                >
                                    <component
                                        :is="
                                            entry.direction === 'deposit'
                                                ? ArrowDownLeftIcon
                                                : ArrowUpRightIcon
                                        "
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block text-sm font-semibold wrap-anywhere text-gray-900 dark:text-white"
                                        >{{ entry.category.name }}</span
                                    >
                                    <span class="block text-xs leading-5 text-gray-500"
                                        >{{
                                            entry.direction === 'deposit' ? 'Deposit' : 'Redemption'
                                        }}
                                        ·
                                        {{
                                            entry.date
                                                ? dayjs(entry.date).format('D MMM')
                                                : 'Monthly total from sheet'
                                        }}</span
                                    >
                                    <span
                                        v-if="entry.note"
                                        class="mt-0.5 block text-xs wrap-anywhere text-gray-600 dark:text-gray-300"
                                        >{{ entry.note }}</span
                                    >
                                </span>
                                <span
                                    class="shrink-0 text-sm font-semibold tabular-nums"
                                    :class="
                                        entry.direction === 'deposit'
                                            ? 'text-emerald-700 dark:text-emerald-400'
                                            : 'text-gray-700 dark:text-gray-300'
                                    "
                                    >{{ entry.direction === 'deposit' ? '+' : '−'
                                    }}{{ rupees(entry.amount_paise) }}</span
                                >
                                <ChevronRightIcon
                                    class="hidden size-4 shrink-0 text-gray-400 sm:block"
                                    aria-hidden="true"
                                />
                            </button>
                        </li>
                    </ul>
                </section>
            </div>

            <div class="contents min-w-0 xl:block xl:space-y-5">
                <section
                    v-if="categoryTotals.length"
                    aria-labelledby="investment-category-heading"
                    class="order-2 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5"
                >
                    <h2
                        id="investment-category-heading"
                        class="border-b border-gray-100 px-5 py-4 text-sm font-semibold text-gray-900 dark:border-white/10 dark:text-white"
                    >
                        Net by category
                    </h2>
                    <ul class="divide-y divide-gray-100 dark:divide-white/10">
                        <li
                            v-for="category in categoryTotals"
                            :key="category.name"
                            class="px-5 py-3"
                        >
                            <div
                                class="flex items-baseline justify-between gap-3 text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                <span class="min-w-0 wrap-anywhere">{{ category.name }}</span
                                ><span class="shrink-0 tabular-nums">{{
                                    rupees(category.deposited - category.redeemed)
                                }}</span>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-gray-500">
                                {{ rupees(category.deposited) }} in ·
                                {{ rupees(category.redeemed) }} redeemed
                            </p>
                        </li>
                    </ul>
                </section>
                <section
                    aria-labelledby="investment-year-heading"
                    class="order-4 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5"
                >
                    <div class="px-5 py-4">
                        <h2
                            id="investment-year-heading"
                            class="text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            {{ selected.period.slice(0, 4) }} summary
                        </h2>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">Net invested</dt>
                                <dd
                                    data-test="year-net"
                                    class="font-semibold text-gray-900 tabular-nums dark:text-white"
                                >
                                    {{ rupees(yearNetPaise) }}
                                </dd>
                            </div>
                            <div v-if="yearTargetPaise !== null" class="flex justify-between gap-3">
                                <dt class="text-gray-500">Planned</dt>
                                <dd
                                    class="font-medium text-gray-800 tabular-nums dark:text-gray-300"
                                >
                                    {{ rupees(yearTargetPaise) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <nav
                        v-if="months.length"
                        aria-label="Monthly net investments"
                        class="border-t border-gray-100 p-2 dark:border-white/10"
                    >
                        <Link
                            v-for="month in months"
                            :key="month.period"
                            :href="'/investments/' + month.period"
                            :aria-current="month.period === selected.period ? 'date' : undefined"
                            class="flex items-center justify-between gap-3 rounded-md px-3 py-2 text-sm"
                            :class="
                                month.period === selected.period
                                    ? 'bg-indigo-50 font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'
                                    : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300'
                            "
                        >
                            <span>{{ dayjs(month.period + '-01').format('MMMM') }}</span
                            ><span class="tabular-nums">{{ rupees(month.netPaise) }}</span>
                        </Link>
                    </nav>
                </section>
            </div>
        </div>

        <InvestmentTransactionEditor
            v-if="editorOpen"
            :entry="editingEntry"
            :categories="categories"
            :default-date="defaultDate"
            @close="editorOpen = false"
        />
    </div>
</template>

<script setup lang="ts">
import PrimaryButton from '@/Components/Button/PrimaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InvestmentTransactionEditor from '@/Components/InvestmentTransactionEditor.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { rupees, type InvestmentSummary, type InvestmentTransaction } from '@/utils/investments';
import {
    ArrowDownLeftIcon,
    ArrowUpRightIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    PlusIcon,
} from '@heroicons/vue/20/solid';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { computed, ref } from 'vue';

const props = defineProps<{
    today: string;
    selected: InvestmentSummary;
    months: InvestmentSummary[];
    transactions: InvestmentTransaction[];
    categories: string[];
    yearNetPaise: number;
    yearTargetPaise: number | null;
}>();

const editorOpen = ref(false);
const editingEntry = ref<InvestmentTransaction | null>(null);
const editingTarget = ref(false);
const targetForm = useForm({ planned: '' });
const firstOfMonth = computed(() => props.selected.period + '-01');
const monthName = computed(() => dayjs(firstOfMonth.value).format('MMMM YYYY'));
const defaultDate = computed(() =>
    props.today.startsWith(props.selected.period) ? props.today : firstOfMonth.value
);

const categoryTotals = computed(() => {
    const totals = new Map<number, { name: string; deposited: number; redeemed: number }>();
    for (const entry of props.transactions) {
        const row = totals.get(entry.category.id) ?? {
            name: entry.category.name,
            deposited: 0,
            redeemed: 0,
        };
        if (entry.direction === 'deposit') {
            row.deposited += entry.amount_paise;
        } else {
            row.redeemed += entry.amount_paise;
        }
        totals.set(entry.category.id, row);
    }
    return Array.from(totals.values()).sort((a, b) => a.name.localeCompare(b.name));
});

const targetProgress = computed(() => {
    const remaining = (props.selected.targetPaise ?? 0) - props.selected.netPaise;
    if (remaining === 0) return 'Target reached';
    return remaining > 0 ? rupees(remaining) + ' to target' : rupees(-remaining) + ' above target';
});

function monthUrl(offset: number) {
    return '/investments/' + dayjs(firstOfMonth.value).add(offset, 'month').format('YYYY-MM');
}

function changeMonth(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.value && input.validity.valid) {
        router.get('/investments/' + input.value);
    } else {
        input.value = props.selected.period;
    }
}

function openEditor(entry: InvestmentTransaction | null) {
    editingTarget.value = false;
    editingEntry.value = entry;
    editorOpen.value = true;
}

function editTarget() {
    targetForm.clearErrors();
    targetForm.planned =
        props.selected.targetPaise === null ? '' : String(props.selected.targetPaise / 100);
    editingTarget.value = true;
}

function saveTarget() {
    targetForm.put('/investments/' + props.selected.period + '/target', {
        preserveScroll: true,
        onSuccess: () => {
            editingTarget.value = false;
        },
    });
}
</script>
