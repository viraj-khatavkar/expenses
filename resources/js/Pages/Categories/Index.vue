<template>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <Link
            v-for="category in categories"
            :key="category.id"
            :href="`/categories/${category.id}/edit`"
        >
            <div class="overflow-hidden rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <div class="px-4 py-5 sm:p-6">
                    {{ category.name }}
                </div>
            </div>
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    categories: {
        type: Array,
        required: true,
    },
});
</script>
