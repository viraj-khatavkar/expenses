<template>
    <TransitionRoot as="template" :show="open">
        <Dialog class="relative z-50" @close="$emit('cancel')">
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-200"
                    enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to="opacity-100 translate-y-0 sm:scale-100"
                    leave="ease-in duration-150"
                    leave-from="opacity-100 translate-y-0 sm:scale-100"
                    leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <DialogPanel
                        class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <DialogTitle class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ title }}
                        </DialogTitle>
                        <p v-if="message" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ message }}
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                class="cursor-pointer rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/10 dark:text-white dark:ring-white/10 dark:hover:bg-white/20"
                                @click="$emit('cancel')"
                            >
                                Cancel
                            </button>
                            <DangerButton data-test="confirm-delete" @click="$emit('confirm')">
                                {{ confirmLabel }}
                            </DangerButton>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import DangerButton from './Button/DangerButton.vue';

withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        message?: string;
        confirmLabel?: string;
    }>(),
    {
        confirmLabel: 'Delete',
    },
);

defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>
