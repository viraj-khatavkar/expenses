<template>
    <div>
        <Form :action="`/subscriptions/${subscription.id}`" method="put" #default="{ errors }">
            <div class="grid max-w-md grid-cols-1 gap-6">
                <TextInput
                    name="title"
                    label="Title"
                    :error="errors.title"
                    v-model="subscription.title"
                />
                <TextInput
                    name="amount"
                    label="Amount"
                    :error="errors.amount"
                    v-model="subscription.amount"
                />
                <SelectInput
                    :options="currencies"
                    name="currency"
                    label="Currency"
                    :error="errors.currency"
                    v-model="subscription.currency"
                />
                <SelectInput
                    :options="frequencies"
                    name="frequency"
                    label="Frequency"
                    :error="errors.frequency"
                    v-model="subscription.frequency"
                />
            </div>
            <div class="mt-6 flex gap-4">
                <PrimaryButton type="submit">Update</PrimaryButton>
                <DangerLinkButton :href="`/subscriptions/${subscription.id}`" method="delete">
                    Delete
                </DangerLinkButton>
            </div>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import DangerLinkButton from '../../Components/Button/DangerLinkButton.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    subscription: {
        id: number;
        title: string;
        amount: number;
        currency: string;
        frequency: string;
    };
}>();

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
