<template>
    <div>
        <Form action="/categories" method="post" #default="{ errors }">
            <TextInput name="name" label="Category Name" :error="errors.name" />
            <PrimaryButton type="submit">Create Category</PrimaryButton>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';
</script>
