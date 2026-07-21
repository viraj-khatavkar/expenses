<template>
    <div class="flex items-start gap-3">
        <div class="flex h-6 shrink-0 items-center">
            <input
                type="checkbox"
                :name="name"
                :id="id"
                value="1"
                v-model="model"
                class="size-4 rounded-sm accent-indigo-600 outline-offset-2 focus-visible:outline-2 focus-visible:outline-indigo-600 dark:accent-indigo-500"
            />
        </div>
        <div>
            <label :for="id" class="block text-sm/6 font-medium text-gray-900 dark:text-white">
                {{ label }}
            </label>
            <p v-if="description" class="text-xs text-gray-400 dark:text-gray-500">
                {{ description }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { generateId } from '@/utils/generateId';

interface PropTypes {
    label: string;
    name: string;
    id?: string;
    description?: string;
}

withDefaults(defineProps<PropTypes>(), {
    id: () => generateId('checkbox-input'),
});

const model = defineModel<boolean>({ default: false });
</script>
