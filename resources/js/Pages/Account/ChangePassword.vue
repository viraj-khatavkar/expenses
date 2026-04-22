<template>
    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Change Password</h2>
        <Form action="/account/password" method="put" class="mt-6" #default="{ errors }">
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
            <PrimaryButton type="submit" class="mt-6">Update Password</PrimaryButton>
        </Form>
    </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';
</script>
