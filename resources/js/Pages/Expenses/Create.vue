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
            <PrimaryButton type="submit">Add Expense</PrimaryButton>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Category } from '@/types/app/Models/Category';
import { Form } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    categories: Category[];
}>();

const defaultDate = dayjs().format('YYYY-MM-DD');
</script>
