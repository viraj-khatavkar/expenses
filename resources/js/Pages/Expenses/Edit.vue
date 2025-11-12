<template>
    <div>
        <Form :action="`/expenses/${expense.id}`" method="put" #default="{ errors }">
            <div class="grid grid-cols-1 md:grid-cols-2 md:gap-6">
                <TextInput
                    type="date"
                    name="date"
                    label="Date"
                    :error="errors.date"
                    v-model="expense.date"
                />
                <TextInput
                    name="amount"
                    label="Amount"
                    :error="errors.amount"
                    v-model="expense.amount"
                />
                <SelectInput
                    :options="categories"
                    name="category_id"
                    label="Category"
                    :error="errors.category_id"
                    v-model="expense.category_id"
                />
            </div>
            <div class="flex gap-4">
                <PrimaryButton type="submit">Update</PrimaryButton>
                <DangerLinkButton :href="`/expenses/${expense.id}`" method="delete">
                    Delete
                </DangerLinkButton>
            </div>
        </Form>
    </div>
</template>

<script setup>
import { Form } from '@inertiajs/vue3';
import SelectInput from '../../Components/Form/SelectInput.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';
import DangerLinkButton from '../../Components/Button/DangerLinkButton.vue';

defineProps({
    expense: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
});
</script>
