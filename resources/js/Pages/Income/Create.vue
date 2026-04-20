<template>
    <div>
        <Form action="/income" method="post" #default="{ errors }">
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
                    :options="sources"
                    name="source_id"
                    label="Source"
                    :error="errors.source_id"
                />
            </div>
            <PrimaryButton type="submit">Add Income</PrimaryButton>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Source } from '@/types/app/Models/Source';
import { Form } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    sources: Source[];
}>();

const defaultDate = dayjs().format('YYYY-MM-DD');
</script>
