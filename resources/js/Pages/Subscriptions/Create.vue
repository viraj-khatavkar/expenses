<template>
    <div>
        <Form action="/subscriptions" method="post" #default="{ errors }">
            <div class="grid max-w-md grid-cols-1 gap-6">
                <TextInput name="title" label="Title" :error="errors.title" />
                <TextInput name="amount" label="Amount" :error="errors.amount" />
                <SelectInput
                    :options="currencies"
                    name="currency"
                    label="Currency"
                    :error="errors.currency"
                />
                <SelectInput
                    :options="frequencies"
                    name="frequency"
                    label="Frequency"
                    :error="errors.frequency"
                />
            </div>
            <PrimaryButton type="submit" class="mt-6">Add Subscription</PrimaryButton>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

const currencies = [
    { id: 'INR', name: '₹ INR' },
    { id: 'USD', name: '$ USD' },
];

const frequencies = [
    { id: 'monthly', name: 'Monthly' },
    { id: 'quarterly', name: 'Quarterly' },
    { id: 'yearly', name: 'Yearly' },
];
</script>
