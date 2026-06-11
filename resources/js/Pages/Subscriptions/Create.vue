<template>
    <div class="mx-auto max-w-3xl">
        <Head title="New Subscription" />

        <PageHeader title="New Subscription" />

        <Form action="/subscriptions" method="post" #default="{ errors, processing }">
            <div class="grid max-w-md grid-cols-1 gap-6">
                <TextInput name="title" label="Title" :error="errors.title" />
                <TextInput
                    name="amount"
                    label="Amount"
                    inputmode="decimal"
                    placeholder="0.00"
                    :error="errors.amount"
                />
                <SelectInput
                    :options="currencies"
                    name="currency"
                    label="Currency"
                    :error="errors.currency"
                    v-model="currency"
                />
                <SelectInput
                    :options="frequencies"
                    name="frequency"
                    label="Frequency"
                    :error="errors.frequency"
                    v-model="frequency"
                />
            </div>
            <div class="mt-8">
                <PrimaryButton type="submit" :disabled="processing">
                    Add Subscription
                </PrimaryButton>
            </div>
        </Form>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const currency = ref('INR');
const frequency = ref('monthly');
</script>
