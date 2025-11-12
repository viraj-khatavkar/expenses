<template>
    <div>
        <Form action="/expenses" method="post" #default="{ errors }">
            <div class="grid grid-cols-1 md:grid-cols-2 md:gap-6">
                <TextInput
                    type="date"
                    name="date"
                    label="Date"
                    :error="errors.date"
                    v-model="defaultDate"
                />
                <TextInput name="amount" label="Amount" :error="errors.amount" />
                <SelectInput
                    :options="categories"
                    name="category_id"
                    label="Category"
                    :error="errors.category_id"
                />
            </div>
            <PrimaryButton type="submit">Create Expense</PrimaryButton>
        </Form>
    </div>
</template>

<script setup>
import { Form } from '@inertiajs/vue3';
import TextInput from '../../Components/Form/TextInput.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import dayjs from 'dayjs';

defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const defaultDate = dayjs().format('YYYY-MM-DD');
</script>
