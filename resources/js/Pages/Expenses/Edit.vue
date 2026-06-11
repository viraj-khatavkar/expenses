<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Edit Expense" />

        <PageHeader title="Edit Expense" />

        <Form :action="`/expenses/${expense.id}`" method="put" #default="{ errors, processing }">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <TextInput
                    type="date"
                    name="date"
                    label="Date"
                    :error="errors.date"
                    v-model="form.date"
                />
                <TextInput
                    type="number"
                    step="0.01"
                    name="amount"
                    label="Amount"
                    inputmode="decimal"
                    :error="errors.amount"
                    v-model="form.amount"
                />
                <SelectInput
                    :options="categories"
                    name="category_id"
                    label="Category"
                    placeholder="Select a category"
                    :error="errors.category_id"
                    v-model="form.category_id"
                />
                <TextInput
                    name="note"
                    label="Note (optional)"
                    placeholder="e.g. Groceries run"
                    :error="errors.note"
                    v-model="form.note"
                />
            </div>
            <div class="mt-8 flex items-center gap-4">
                <PrimaryButton type="submit" :disabled="processing">Update</PrimaryButton>
                <DangerButton type="button" @click="confirmingDelete = true">
                    Delete
                </DangerButton>
            </div>
        </Form>

        <ConfirmDialog
            :open="confirmingDelete"
            title="Delete this expense?"
            :message="`This will permanently remove the ${currencyFormatter(form.amount)} expense.`"
            @cancel="confirmingDelete = false"
            @confirm="destroyExpense"
        />
    </div>
</template>

<script setup lang="ts">
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Category } from '@/types/app/Models/Category';
import { Expense } from '@/types/app/Models/Expense';
import currencyFormatter from '@/utils/currencyFormatter';
import { Form, Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import DangerButton from '../../Components/Button/DangerButton.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

const props = defineProps<{
    expense: Expense;
    categories: Category[];
}>();

const form = reactive({
    date: props.expense.date,
    amount: props.expense.amount,
    category_id: props.expense.category_id,
    note: props.expense.note ?? '',
});

const confirmingDelete = ref(false);

const destroyExpense = () => {
    router.delete(`/expenses/${props.expense.id}`);
};
</script>
