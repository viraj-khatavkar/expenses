<template>
    <div>
        <Form :action="`/income/${income.id}`" method="put" #default="{ errors }">
            <div class="grid grid-cols-1 md:grid-cols-2 md:gap-6">
                <TextInput
                    type="date"
                    name="date"
                    label="Date"
                    :error="errors.date"
                    v-model="income.date"
                />
                <TextInput
                    name="amount"
                    label="Amount"
                    :error="errors.amount"
                    v-model="income.amount"
                />
                <SelectInput
                    :options="sources"
                    name="source_id"
                    label="Source"
                    :error="errors.source_id"
                    v-model="income.source_id"
                />
            </div>
            <div class="flex gap-4">
                <PrimaryButton type="submit">Update</PrimaryButton>
                <DangerLinkButton :href="`/income/${income.id}`" method="delete">
                    Delete
                </DangerLinkButton>
            </div>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Income } from '@/types/app/Models/Income';
import { Source } from '@/types/app/Models/Source';
import { Form } from '@inertiajs/vue3';
import DangerLinkButton from '../../Components/Button/DangerLinkButton.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    income: Income;
    sources: Source[];
}>();
</script>
