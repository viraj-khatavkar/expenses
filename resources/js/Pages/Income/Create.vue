<template>
    <div class="mx-auto max-w-3xl">
        <Head title="New Income" />

        <PageHeader title="New Income" />

        <Form action="/income" method="post" #default="{ errors, processing }">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <TextInput
                    type="date"
                    name="date"
                    label="Date"
                    :error="errors.date"
                    v-model="defaultDate"
                />
                <TextInput
                    name="amount"
                    label="Amount"
                    inputmode="decimal"
                    placeholder="0.00"
                    :error="errors.amount"
                />
                <SelectInput
                    :options="sources"
                    name="source_id"
                    label="Source"
                    placeholder="Select a source"
                    :error="errors.source_id"
                />
            </div>
            <div class="mt-8">
                <PrimaryButton type="submit" :disabled="processing">Add Income</PrimaryButton>
            </div>
        </Form>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { Source } from '@/types/app/Models/Source';
import { Form, Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    sources: Source[];
}>();

const defaultDate = dayjs().format('YYYY-MM-DD');
</script>
