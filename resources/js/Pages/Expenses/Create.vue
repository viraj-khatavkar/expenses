<template>
    <div class="mx-auto max-w-3xl">
        <Head title="New Expense" />

        <PageHeader title="New Expense" />

        <Form action="/expenses" method="post" #default="{ errors, processing }">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <TextInput
                    type="date"
                    name="date"
                    label="Date"
                    :error="errors.date"
                    v-model="defaultDate"
                />
                <TextInput
                    type="number"
                    step="0.01"
                    name="amount"
                    label="Amount"
                    inputmode="decimal"
                    placeholder="0.00"
                    :error="errors.amount"
                />
                <SelectInput
                    :options="categories"
                    name="category_id"
                    label="Category"
                    placeholder="Select a category"
                    :error="errors.category_id"
                />
                <TextInput
                    name="note"
                    label="Note (optional)"
                    placeholder="e.g. Groceries run"
                    :error="errors.note"
                />
            </div>
            <div class="mt-8">
                <PrimaryButton type="submit" :disabled="processing">Add Expense</PrimaryButton>
            </div>
        </Form>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { Category } from '@/types/app/Models/Category';
import { Form, Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    categories: Category[];
}>();

const defaultDate = dayjs().format('YYYY-MM-DD');
</script>
