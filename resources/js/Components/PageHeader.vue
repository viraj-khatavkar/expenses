<template>
    <header class="mb-6 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
            <p v-if="subtitle" class="mt-0.5 text-sm text-gray-400 dark:text-gray-500">
                {{ subtitle }}
            </p>
        </div>
        <div v-if="$slots.default" class="flex items-center gap-3">
            <slot />
        </div>
    </header>
</template>

<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string;
}>();
</script>
