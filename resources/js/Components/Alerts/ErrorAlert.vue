<template>
    <div class="rounded-md bg-red-50 p-4 dark:bg-red-500/10 dark:outline dark:outline-red-500/20">
        <div class="flex">
            <div class="shrink-0">
                <XCircleIcon class="size-5 text-red-400" aria-hidden="true" />
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800 dark:text-red-300">
                    <slot />
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { XCircleIcon } from '@heroicons/vue/20/solid';
</script>
