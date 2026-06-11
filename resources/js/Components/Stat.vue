<template>
    <div
        class="rounded-xl bg-gray-50 px-6 py-6 shadow-sm ring-1 ring-gray-950/5 sm:px-8 dark:bg-white/5 dark:ring-white/10"
    >
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
            <slot name="header"></slot>
        </p>
        <p
            class="mt-2 text-3xl font-bold tracking-wide text-gray-900 sm:text-4xl dark:text-white"
        >
            <slot />
        </p>
        <p v-if="$slots.footer" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            <slot name="footer" />
        </p>
    </div>
</template>
<script setup lang="ts"></script>
