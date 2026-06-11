<template>
    <div class="mx-auto max-w-3xl">
        <Head title="Change Password" />

        <PageHeader title="Change Password" />

        <Form action="/account/password" method="put" #default="{ errors, processing }">
            <div class="grid max-w-md grid-cols-1 gap-6">
                <TextInput
                    type="password"
                    name="current_password"
                    label="Current Password"
                    :error="errors.current_password"
                />
                <TextInput
                    type="password"
                    name="password"
                    label="New Password"
                    :error="errors.password"
                />
                <TextInput
                    type="password"
                    name="password_confirmation"
                    label="Confirm New Password"
                    :error="errors.password_confirmation"
                />
            </div>
            <div class="mt-8">
                <PrimaryButton type="submit" :disabled="processing">
                    Update Password
                </PrimaryButton>
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
