<template>
    <div>
        <div class="mb-5 flex justify-end">
            <Link href="/categories/create">
                <PrimaryButton>Add Category</PrimaryButton>
            </Link>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <Link
                v-for="category in categories"
                :key="category.id"
                :href="`/categories/${category.id}/edit`"
            >
                <div class="overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800/50">
                    <div class="px-4 py-5 sm:p-6">
                        {{ category.name }}
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Category } from '@/types/app/Models/Category';
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';

defineProps<{
    categories: Category[];
}>();
</script>
