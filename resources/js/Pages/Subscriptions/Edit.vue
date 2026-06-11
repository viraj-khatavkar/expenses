<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Edit Subscription" />

        <PageHeader title="Edit Subscription" />

        <Form
            :action="`/subscriptions/${subscription.id}`"
            method="put"
            #default="{ errors, processing }"
        >
            <div class="grid max-w-md grid-cols-1 gap-6">
                <TextInput
                    name="title"
                    label="Title"
                    :error="errors.title"
                    v-model="form.title"
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
                    :options="currencies"
                    name="currency"
                    label="Currency"
                    :error="errors.currency"
                    v-model="form.currency"
                />
                <SelectInput
                    :options="frequencies"
                    name="frequency"
                    label="Frequency"
                    :error="errors.frequency"
                    v-model="form.frequency"
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
            title="Delete this subscription?"
            :message="`${form.title} will be removed from your subscriptions.`"
            @cancel="confirmingDelete = false"
            @confirm="destroySubscription"
        />
    </div>
</template>

<script setup lang="ts">
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Form, Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import DangerButton from '../../Components/Button/DangerButton.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

const props = defineProps<{
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

const form = reactive({
    title: props.subscription.title,
    amount: props.subscription.amount,
    currency: props.subscription.currency,
    frequency: props.subscription.frequency,
});

const confirmingDelete = ref(false);

const destroySubscription = () => {
    router.delete(`/subscriptions/${props.subscription.id}`);
};
</script>
