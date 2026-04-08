<template>
    <div class="mx-auto max-w-3xl">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ fyLabel }}
        </h1>
        <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">
            Your spending story so far
        </p>

        <!-- Empty state -->
        <div
            v-if="empty"
            class="mt-12 rounded-xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
        >
            <p class="text-sm text-gray-400 dark:text-gray-500">
                No expenses recorded yet. Start tracking to see your story
                unfold.
            </p>
        </div>

        <template v-else>
            <!-- The Anchor — your year in one glance -->
            <div
                class="mt-8 rounded-xl bg-gray-50 px-6 py-8 shadow-sm ring-1 ring-gray-950/5 sm:px-8 dark:bg-white/5 dark:ring-white/10"
            >
                <p
                    class="text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                >
                    Over
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ overview.monthCount }}
                        {{ overview.monthCount === 1 ? 'month' : 'months' }}
                    </span>
                    you reached for your wallet
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{
                            overview.transactionCount.toLocaleString('en-IN')
                        }}
                        times </span
                    >, spending a total of
                </p>
                <p
                    class="mt-3 text-4xl font-bold tracking-wide text-gray-900 sm:text-5xl dark:text-white"
                >
                    {{ compactCurrencyFormatter(overview.total) }}
                </p>
                <div
                    class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500 dark:text-gray-400"
                >
                    <span>
                        <span
                            class="font-medium text-gray-900 dark:text-white"
                            >{{
                                compactCurrencyFormatter(overview.avgMonthly)
                            }}</span
                        >
                        /month
                    </span>
                    <span>
                        <span
                            class="font-medium text-gray-900 dark:text-white"
                            >{{
                                compactCurrencyFormatter(
                                    overview.avgPerTransaction,
                                )
                            }}</span
                        >
                        /transaction avg
                    </span>
                    <span>
                        Spent on
                        <span
                            class="font-medium text-gray-900 dark:text-white"
                            >{{ overview.spendingDays }}</span
                        >
                        of {{ overview.totalDaysInRange }} days
                    </span>
                </div>
                <p
                    class="mt-4 text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                >
                    At this pace, you're on track to spend
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{
                            compactCurrencyFormatter(overview.projectedYearly)
                        }}
                    </span>
                    by March.
                    <template v-if="overview.trend !== null">
                        <span
                            :class="
                                overview.trend > 0
                                    ? 'text-rose-500'
                                    : 'text-emerald-600 dark:text-emerald-400'
                            "
                        >
                            Your recent months are
                            {{ overview.trend > 0 ? 'running' : 'trending' }}
                            <span class="font-semibold"
                                >{{ Math.abs(overview.trend) }}%
                                {{
                                    overview.trend > 0 ? 'higher' : 'lower'
                                }}</span
                            >
                            than your earlier months.
                        </span>
                    </template>
                </p>
            </div>

            <!-- Insights — deferred, loads after initial render -->
            <Deferred data="insights">
                <template #fallback>
                    <div class="mt-8 space-y-5">
                        <div
                            v-for="i in 4"
                            :key="i"
                            class="h-24 animate-pulse rounded-xl bg-gray-100 dark:bg-white/5"
                        ></div>
                    </div>
                </template>

            <div class="mt-8 space-y-5">
                <!-- Spike Month -->
                <article
                    v-if="insights.spikeMonth"
                    class="rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="mt-0.5 h-8 w-1 shrink-0 rounded-full bg-rose-400"
                        ></div>
                        <div>
                            <p
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                {{ insights.spikeMonth.month }} broke your
                                pattern
                            </p>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                At
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{
                                        compactCurrencyFormatter(
                                            insights.spikeMonth.total,
                                        )
                                    }}</span
                                >, it was
                                <span
                                    class="font-medium text-rose-500 dark:text-rose-400"
                                    >{{ insights.spikeMonth.ratio }}x</span
                                >
                                your typical month. The biggest driver was
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{ insights.spikeMonth.driver }}</span
                                >. Was this a planned spike or did it creep up
                                on you?
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Your Spending Rhythm -->
                <article
                    class="rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="mt-0.5 h-8 w-1 shrink-0 rounded-full bg-amber-400"
                        ></div>
                        <div class="flex-1">
                            <p
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Your spending rhythm
                            </p>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                <template v-if="peakDay">
                                    Your spending energy peaks on
                                    <span
                                        class="font-medium text-amber-600 dark:text-amber-400"
                                        >{{ peakDayFull }}s</span
                                    >.
                                </template>
                                <template
                                    v-if="
                                        insights.weekendAvg >
                                        insights.weekdayAvg
                                    "
                                >
                                    Each weekend transaction averages
                                    <span
                                        class="font-medium text-amber-600 dark:text-amber-400"
                                        >{{
                                            compactCurrencyFormatter(
                                                insights.weekendAvg,
                                            )
                                        }}</span
                                    >
                                    vs
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                        >{{
                                            compactCurrencyFormatter(
                                                insights.weekdayAvg,
                                            )
                                        }}</span
                                    >
                                    on weekdays — weekends loosen the purse
                                    strings.
                                </template>
                            </p>
                            <!-- Mini bar chart -->
                            <div class="mt-4 flex items-end gap-1.5">
                                <div
                                    v-for="day in insights.spendingByDay"
                                    :key="day.day"
                                    class="flex flex-1 flex-col items-center gap-1"
                                >
                                    <div
                                        class="w-full rounded-sm transition-all duration-300"
                                        :class="
                                            day.day === peakDay
                                                ? 'bg-amber-400 dark:bg-amber-500'
                                                : 'bg-gray-200 dark:bg-gray-700'
                                        "
                                        :style="{
                                            height:
                                                maxDayTotal > 0
                                                    ? Math.max(
                                                          4,
                                                          (day.total /
                                                              maxDayTotal) *
                                                              48,
                                                      ) + 'px'
                                                    : '4px',
                                        }"
                                    ></div>
                                    <span
                                        class="text-[10px]"
                                        :class="
                                            day.day === peakDay
                                                ? 'font-semibold text-amber-600 dark:text-amber-400'
                                                : 'text-gray-400 dark:text-gray-500'
                                        "
                                    >
                                        {{ day.day }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Your Habit -->
                <article
                    class="rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="mt-0.5 h-8 w-1 shrink-0 rounded-full bg-indigo-400"
                        ></div>
                        <div>
                            <p
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Your most consistent habit
                            </p>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                You turned to
                                <span
                                    class="font-medium text-indigo-500 dark:text-indigo-400"
                                    >{{ insights.topHabit.category }}</span
                                >
                                {{ ' ' }}
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{ insights.topHabit.count }} times</span
                                >
                                <template v-if="insights.topHabit.isRecurring">
                                    — every single month </template
                                >, averaging
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{
                                        compactCurrencyFormatter(
                                            insights.topHabit.avgPerVisit,
                                        )
                                    }}
                                    each time</span
                                >. If this continues unchanged, it'll cost
                                <span
                                    class="font-medium text-indigo-500 dark:text-indigo-400"
                                    >{{
                                        compactCurrencyFormatter(
                                            insights.topHabit.projectedYearly,
                                        )
                                    }}</span
                                >
                                this year alone.
                            </p>
                        </div>
                    </div>
                </article>

                <!-- The Small Purchase Effect -->
                <article
                    v-if="insights.smallPurchase"
                    class="rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="mt-0.5 h-8 w-1 shrink-0 rounded-full bg-violet-400"
                        ></div>
                        <div>
                            <p
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Death by a thousand small cuts
                            </p>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    class="font-medium text-violet-500 dark:text-violet-400"
                                    >{{
                                        insights.smallPurchase.count
                                    }}
                                    purchases</span
                                >
                                under ₹2,000 — roughly
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{
                                        insights.smallPurchase.perWeek
                                    }}
                                    per week</span
                                >
                                — quietly added up to
                                <span
                                    class="font-medium text-violet-500 dark:text-violet-400"
                                    >{{
                                        compactCurrencyFormatter(
                                            insights.smallPurchase.total,
                                        )
                                    }}</span
                                >. That's
                                <span
                                    class="font-medium text-violet-500 dark:text-violet-400"
                                    >{{ insights.smallPurchase.percent }}%</span
                                >
                                of your total spend, one small swipe at a time.
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Spending Gravity -->
                <article
                    v-if="insights.concentration.percent >= 60"
                    class="rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="mt-0.5 h-8 w-1 shrink-0 rounded-full bg-emerald-400"
                        ></div>
                        <div>
                            <p
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Your spending gravity
                            </p>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    class="font-medium text-emerald-600 dark:text-emerald-400"
                                    >{{
                                        insights.concentration.names.join(
                                            ', ',
                                        )
                                    }}</span
                                >
                                — just
                                {{
                                    insights.concentration.names.length
                                }}
                                categories absorb
                                <span
                                    class="font-medium text-emerald-600 dark:text-emerald-400"
                                    >{{
                                        insights.concentration.percent
                                    }}%</span
                                >
                                of everything.
                                <template
                                    v-if="
                                        insights.concentration.remainingCount >
                                        0
                                    "
                                >
                                    The remaining
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                        >{{
                                            compactCurrencyFormatter(
                                                insights.concentration
                                                    .remainingTotal,
                                            )
                                        }}</span
                                    >
                                    is spread across
                                    {{ insights.concentration.remainingCount }}
                                    other categories.
                                </template>
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Most Expensive Day -->
                <article
                    class="rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="mt-0.5 h-8 w-1 shrink-0 rounded-full bg-gray-400"
                        ></div>
                        <div>
                            <p
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Your most expensive day
                            </p>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                On
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{ insights.biggestDay.date }}</span
                                >, you spent
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{
                                        compactCurrencyFormatter(
                                            insights.biggestDay.total,
                                        )
                                    }}</span
                                >
                                across
                                {{ insights.biggestDay.count }}
                                {{
                                    insights.biggestDay.count === 1
                                        ? 'transaction'
                                        : 'transactions'
                                }}
                                <template
                                    v-if="
                                        insights.biggestDay.categories.length >
                                        0
                                    "
                                >
                                    in
                                    {{
                                        insights.biggestDay.categories.join(
                                            ', ',
                                        )
                                    }}
                                </template>
                                <template
                                    v-if="insights.biggestDay.multiple >= 2"
                                >
                                    — that's
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                        >{{
                                            insights.biggestDay.multiple
                                        }}x</span
                                    >
                                    what you typically spend in a day.
                                </template>
                            </p>
                        </div>
                    </div>
                </article>
            </div>
            </Deferred>
        </template>
    </div>
</template>

<script setup lang="ts">
import compactCurrencyFormatter from '@/utils/compactCurrencyFormatter';
import currencyFormatter from '@/utils/currencyFormatter';
import { Deferred } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    fyLabel: string;
    empty: boolean;
    overview?: {
        total: number;
        transactionCount: number;
        monthCount: number;
        avgMonthly: number;
        projectedYearly: number;
        avgPerTransaction: number;
        spendingDays: number;
        totalDaysInRange: number;
        trend: number | null;
    };
    insights?: {
        spikeMonth: {
            month: string;
            total: number;
            ratio: number;
            driver: string;
        } | null;
        spendingByDay: { day: string; total: number; count: number }[];
        weekendPercent: number;
        weekdayAvg: number;
        weekendAvg: number;
        topHabit: {
            category: string;
            count: number;
            total: number;
            avgPerVisit: number;
            isRecurring: boolean;
            projectedYearly: number;
        };
        smallPurchase: {
            count: number;
            total: number;
            percent: number;
            perWeek: number;
        } | null;
        concentration: {
            names: string[];
            percent: number;
            remainingCount: number;
            remainingTotal: number;
        };
        biggestDay: {
            date: string;
            total: number;
            count: number;
            categories: string[];
            multiple: number;
        };
    };
}>();

const maxDayTotal = computed(() =>
    Math.max(...(props.insights?.spendingByDay.map((d) => d.total) ?? [0]), 0),
);

const peakDay = computed(() => {
    const days = props.insights?.spendingByDay ?? [];
    const peak = days.reduce(
        (max, d) => (d.total > max.total ? d : max),
        { day: '', total: 0 },
    );
    return peak.total > 0 ? peak.day : null;
});

const dayNames: Record<string, string> = {
    Mon: 'Monday',
    Tue: 'Tuesday',
    Wed: 'Wednesday',
    Thu: 'Thursday',
    Fri: 'Friday',
    Sat: 'Saturday',
    Sun: 'Sunday',
};

const peakDayFull = computed(() =>
    peakDay.value ? dayNames[peakDay.value] : null,
);
</script>
