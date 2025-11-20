<template>
    <div>
        <Stat id="yearly-stat">
            <template #header> This Year</template>
            <template #default>
                {{ currencyFormatter(thisYearTotal) }}
            </template>
        </Stat>

        <Divider>Monthly</Divider>

        <div class="grid grid-cols-1 lg:grid-cols-3">
            <div v-for="monthlyStat in thisYearMonthlyTotals" :key="monthlyStat.month">
                <Stat :id="monthlyStat.month">
                    <template #header>{{ monthlyStat.month }}</template>
                    <template #default>{{ currencyFormatter(monthlyStat.total) }}</template>
                </Stat>
            </div>
        </div>

        <Divider> Categories</Divider>

        <div class="grid grid-cols-1 lg:grid-cols-3">
            <div v-for="categoryStat in thisYearCategoryTotals" :key="categoryStat.category">
                <Stat :id="categoryStat.category">
                    <template #header>{{ categoryStat.category }}</template>
                    <template #default>{{ currencyFormatter(categoryStat.total) }}</template>
                </Stat>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Divider from '@/Components/Divider.vue';
import Stat from '@/Components/Stat.vue';
import currencyFormatter from '@/utils/currencyFormatter';

defineProps<{
    thisYearTotal: number;
    thisYearMonthlyTotals: { month: string; total: number }[];
    thisYearCategoryTotals: { category: string; total: number }[];
}>();
</script>
