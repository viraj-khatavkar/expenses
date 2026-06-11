<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Edit Source" />

        <PageHeader title="Edit Source" />

        <Form :action="`/sources/${source.id}`" method="put" #default="{ errors, processing }">
            <div class="max-w-md">
                <TextInput
                    name="name"
                    label="Source Name"
                    :error="errors.name"
                    v-model="form.name"
                />
            </div>
            <div class="mt-8">
                <PrimaryButton type="submit" :disabled="processing">Update</PrimaryButton>
            </div>
        </Form>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { Source } from '@/types/app/Models/Source';
import { Form, Head } from '@inertiajs/vue3';
import { reactive } from 'vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';

const props = defineProps<{
    source: Source;
}>();

const form = reactive({
    name: props.source.name,
});
</script>
