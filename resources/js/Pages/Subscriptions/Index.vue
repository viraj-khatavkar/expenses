<template>
    <div>
        <!-- Analytics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div
                v-for="total in totals"
                :key="total.currency"
                class="rounded-xl bg-gray-50 px-5 py-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500">
                    {{ total.currency }} Subscriptions
                </p>
                <p
                    class="mt-1 text-2xl font-bold tracking-wide text-gray-900 dark:text-white"
                >
                    {{ formatAmount(total.monthly, total.currency) }}
                    <span class="text-sm font-normal text-gray-400 dark:text-gray-500">/mo</span>
                </p>
                <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">
                    {{ formatAmount(total.yearly, total.currency) }}/yr
                </p>
            </div>
        </div>

        <!-- Add button + List -->
        <div class="mt-8">
            <div class="mb-5 flex justify-end">
                <Link href="/subscriptions/create">
                    <PrimaryButton>Add Subscription</PrimaryButton>
                </Link>
            </div>

            <div
                v-if="subscriptions.length > 0"
                class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <ul class="divide-y divide-gray-950/5 dark:divide-white/10">
                    <li v-for="subscription in subscriptions" :key="subscription.id">
                        <Link
                            :href="`/subscriptions/${subscription.id}/edit`"
                            class="flex items-center justify-between px-5 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{ subscription.title }}
                                </p>
                                <p
                                    class="mt-0.5 text-xs text-gray-400 capitalize dark:text-gray-500"
                                >
                                    {{ subscription.frequency }}
                                </p>
                            </div>
                            <span
                                class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white"
                            >
                                {{ formatAmount(subscription.amount, subscription.currency) }}
                            </span>
                        </Link>
                    </li>
                </ul>
            </div>

            <div
                v-else
                class="rounded-xl bg-white px-5 py-12 text-center text-sm text-gray-400 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-500 dark:ring-white/10"
            >
                No subscriptions yet.
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';

defineProps<{
    subscriptions: {
        id: number;
        title: string;
        amount: number;
        currency: string;
        frequency: string;
    }[];
    totals: {
        currency: string;
        monthly: number;
        yearly: number;
    }[];
}>();

function formatAmount(amount: number, currency: string): string {
    const locale = currency === 'USD' ? 'en-US' : 'en-IN';
    return new Intl.NumberFormat(locale, { style: 'currency', currency }).format(amount);
}
</script>
