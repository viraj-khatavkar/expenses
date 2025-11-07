<template>
    <Form :action="`/categories/${category.id}`" method="put" #default="{ errors }">
        <TextInput name="name" label="Category Name" :error="errors.name" v-model="category.name" />
        <PrimaryButton type="submit">Update</PrimaryButton>
    </Form>
</template>

<script setup>
import { Form } from '@inertiajs/vue3';
import TextInput from '../../Components/Form/TextInput.vue';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';

defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>
