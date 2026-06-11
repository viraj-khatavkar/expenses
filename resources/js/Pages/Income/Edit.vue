<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Edit Income" />

        <PageHeader title="Edit Income" />

        <Form :action="`/income/${income.id}`" method="put" #default="{ errors, processing }">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <TextInput
                    type="date"
                    name="date"
                    label="Date"
                    :error="errors.date"
                    v-model="form.date"
                />
                <TextInput
                    name="amount"
                    label="Amount"
                    inputmode="decimal"
                    :error="errors.amount"
                    v-model="form.amount"
                />
                <SelectInput
                    :options="sources"
                    name="source_id"
                    label="Source"
                    placeholder="Select a source"
                    :error="errors.source_id"
                    v-model="form.source_id"
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
            title="Delete this income?"
            :message="`This will permanently remove the ${currencyFormatter(form.amount)} income entry.`"
            @cancel="confirmingDelete = false"
            @confirm="destroyIncome"
        />
    </div>
</template>

<script setup lang="ts">
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Income } from '@/types/app/Models/Income';
import { Source } from '@/types/app/Models/Source';
import currencyFormatter from '@/utils/currencyFormatter';
import { Form, Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import DangerButton from '../../Components/Button/DangerButton.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

const props = defineProps<{
    income: Income;
    sources: Source[];
}>();

const form = reactive({
    date: props.income.date,
    amount: props.income.amount,
    source_id: props.income.source_id,
});

const confirmingDelete = ref(false);

const destroyIncome = () => {
    router.delete(`/income/${props.income.id}`);
};
</script>
