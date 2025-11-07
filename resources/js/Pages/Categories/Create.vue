<template>
    <div>
        <Form action="/categories" method="post" #default="{ errors }">
            <TextInput name="name" label="Category Name" :error="errors.name" />
            <PrimaryButton type="submit">Create Category</PrimaryButton>
        </Form>
    </div>
</template>

<script setup>
import { Form, Link } from '@inertiajs/vue3';
import TextInput from '../../Components/Form/TextInput.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
</script>
