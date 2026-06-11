<template>
    <div>
        <label :for="id" class="block text-sm/6 font-medium text-gray-900 dark:text-white">
            {{ label }}
        </label>
        <div class="mt-2 grid grid-cols-1">
            <select
                :id="id"
                :name="name"
                v-model="proxy"
                :aria-invalid="error ? 'true' : undefined"
                :aria-describedby="error ? `${name}-error` : undefined"
                class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:*:bg-gray-800 dark:focus-visible:outline-indigo-500"
                :class="{
                    'text-gray-400 dark:text-gray-500': proxy === '',
                    'outline-red-300 focus-visible:outline-red-600': error,
                }"
            >
                <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
                <option v-for="option in options" :key="option.id" :value="option.id">
                    {{ option.name }}
                </option>
            </select>
            <ChevronDownIcon
                class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400"
                aria-hidden="true"
            />
        </div>
        <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400" :id="`${name}-error`">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { generateId } from '@/utils/generateId';
import { ChevronDownIcon } from '@heroicons/vue/16/solid';
import { computed } from 'vue';

interface Option {
    id: number | string;
    name: string;
}

interface Props {
    label: string;
    name: string;
    id?: string;
    options: Option[];
    placeholder?: string;
    error?: string;
}

withDefaults(defineProps<Props>(), {
    id: () => generateId('select-input'),
});

const model = defineModel<number | string | null>();

const proxy = computed({
    get: () => model.value ?? '',
    set: (value) => (model.value = value),
});
</script>
