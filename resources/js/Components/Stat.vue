<template>
    <div class="flex items-center bg-white dark:bg-gray-900">
        <div class="mx-auto max-w-7xl">
            <div class="grid grid-cols-1 bg-gray-900/5 dark:bg-white/10">
                <div class="bg-white px-4 py-6 sm:px-6 lg:px-8 dark:bg-gray-900">
                    <p class="text-sm/6 font-medium text-gray-500 dark:text-gray-400">
                        <slot name="header"></slot>
                    </p>
                    <p class="mt-2 flex items-baseline gap-x-2">
                        <span
                            class="text-4xl font-semibold tracking-wider text-gray-900 dark:text-white"
                        >
                            <slot />
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts"></script>
