<template>
    <Form action="/categories" method="post" #default="{ errors }">
        <TextInput name="name" label="Category Name" :error="errors.name" />
        <PrimaryButton type="submit">Create Category</PrimaryButton>
    </Form>
</template>

<script setup>
import { Form } from '@inertiajs/vue3';
import TextInput from '../../Components/Form/TextInput.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
</script>
