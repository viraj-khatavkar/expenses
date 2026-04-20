<template>
    <Form :action="`/sources/${source.id}`" method="put" #default="{ errors }">
        <TextInput name="name" label="Source Name" :error="errors.name" v-model="source.name" />
        <PrimaryButton type="submit">Update</PrimaryButton>
    </Form>
</template>

<script setup lang="ts">
import { Source } from '@/types/app/Models/Source';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';

defineProps<{
    source: Source;
}>();
</script>
