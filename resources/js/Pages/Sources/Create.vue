<template>
    <div>
        <Form action="/sources" method="post" #default="{ errors }">
            <TextInput name="name" label="Source Name" :error="errors.name" />
            <PrimaryButton type="submit">Create Source</PrimaryButton>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';
</script>
