<template>
    <div>
        <div class="mb-5 flex justify-end">
            <Link href="/sources/create">
                <PrimaryButton>Add Source</PrimaryButton>
            </Link>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <Link
                v-for="source in sources"
                :key="source.id"
                :href="`/sources/${source.id}/edit`"
            >
                <div class="overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800/50">
                    <div class="px-4 py-5 sm:p-6">
                        {{ source.name }}
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Source } from '@/types/app/Models/Source';
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/Button/PrimaryButton.vue';

defineProps<{
    sources: Source[];
}>();
</script>
