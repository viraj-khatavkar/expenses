<template>
    <div class="mx-auto max-w-3xl">
        <Head title="New Source" />

        <PageHeader title="New Source" />

        <Form action="/sources" method="post" #default="{ errors, processing }">
            <div class="max-w-md">
                <TextInput name="name" label="Source Name" :error="errors.name" />
            </div>
            <div class="mt-8">
                <PrimaryButton type="submit" :disabled="processing">Create Source</PrimaryButton>
            </div>
        </Form>
    </div>
</template>

<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import { Form, Head } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';
</script>
