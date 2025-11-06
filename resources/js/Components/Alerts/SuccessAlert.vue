<template>
    <div
        class="rounded-md bg-green-50 p-4 dark:bg-green-500/10 dark:outline dark:outline-green-500/20"
    >
        <div class="flex">
            <div class="shrink-0">
                <CheckCircleIcon class="size-5 text-green-400" aria-hidden="true" />
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                    <slot />
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { CheckCircleIcon, XMarkIcon } from '@heroicons/vue/20/solid';
</script>
