<template>
    <Form :action="`/categories/${category.id}`" method="put" #default="{ errors }">
        <TextInput name="name" label="Category Name" :error="errors.name" v-model="category.name" />
        <PrimaryButton type="submit">Update</PrimaryButton>
    </Form>
</template>

<script setup lang="ts">
import { Category } from '@/types/app/Models/Category';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    category: Category;
}>();
</script>
