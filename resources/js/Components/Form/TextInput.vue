<template>
    <div>
        <label :for="id" class="block text-sm/6 font-medium text-gray-900 dark:text-white">
            {{ label }}
        </label>
        <div class="mt-2">
            <input
                :type="type"
                :name="name"
                :id="id"
                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500"
                :placeholder="placeholder"
                v-model="model"
                :class="{
                    'text-red-900 outline-red-300 placeholder:text-red-400 focus:outline-red-600':
                        error,
                }"
            />
        </div>
        <p
            v-if="error"
            class="mt-2 text-sm text-red-600 dark:text-red-400"
            id="email-error"
        >
            {{ error }}
        </p>
    </div>
</template>

<script setup>
const props = defineProps({
    type: {
        type: String,
        default: 'text',
    },
    label: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
    id: {
        type: String,
        required: false,
        default: `text-input-${crypto.randomUUID()}`,
    },
    placeholder: {
        type: String,
        required: false,
        default: '',
    },
    error: {
        type: String,
        required: false,
        default: null,
    },
});

const model = defineModel();
</script>
