<template>
    <Dialog :open="true" class="relative z-50" @close="close">
        <div class="fixed inset-0 bg-gray-900/50" aria-hidden="true" />
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <DialogPanel
                    class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900"
                >
                    <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ entry ? 'Edit transaction' : 'Add transaction' }}
                    </DialogTitle>
                    <form class="mt-5" @submit.prevent="save">
                        <fieldset
                            class="grid grid-cols-2 gap-1 rounded-lg bg-gray-100 p-1 dark:bg-white/10"
                        >
                            <legend class="sr-only">Transaction type</legend>
                            <label
                                v-for="option in directions"
                                :key="option.value"
                                class="cursor-pointer rounded-md px-3 py-2 text-center text-sm font-semibold has-focus-visible:outline-2 has-focus-visible:outline-indigo-600"
                                :class="
                                    form.direction === option.value
                                        ? 'bg-white text-indigo-700 shadow-xs dark:bg-gray-800 dark:text-indigo-300'
                                        : 'text-gray-600 dark:text-gray-300'
                                "
                            >
                                <input
                                    v-model="form.direction"
                                    class="sr-only"
                                    type="radio"
                                    name="direction"
                                    :value="option.value"
                                />
                                {{ option.label }}
                            </label>
                        </fieldset>
                        <p v-if="form.errors.direction" class="mt-1 text-sm text-red-600">
                            {{ form.errors.direction }}
                        </p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <TextInput
                                v-model="form.amount"
                                label="Amount (₹)"
                                name="amount"
                                type="number"
                                inputmode="decimal"
                                min="0.01"
                                max="9999999999.99"
                                step="0.01"
                                :error="form.errors.amount"
                            />
                            <TextInput
                                v-model="form.date"
                                :label="monthOnly ? 'Month' : 'Date'"
                                name="date"
                                :type="monthOnly ? 'month' : 'date'"
                                :error="form.errors.date"
                            />
                            <div class="sm:col-span-2">
                                <TextInput
                                    v-model="form.category"
                                    label="Category"
                                    name="category"
                                    list="investment-category-options"
                                    placeholder="Select or type a category"
                                    :error="form.errors.category"
                                />
                                <datalist id="investment-category-options">
                                    <option v-for="name in categories" :key="name" :value="name" />
                                </datalist>
                            </div>
                            <TextInput
                                v-model="form.note"
                                class="sm:col-span-2"
                                label="Note (optional)"
                                name="note"
                                :error="form.errors.note"
                            />
                        </div>
                        <p v-if="monthOnly" class="mt-3 text-xs text-gray-500">
                            Your sheet gives a monthly total for this entry.
                        </p>
                        <div class="mt-6 flex items-center gap-3">
                            <button
                                v-if="entry"
                                type="button"
                                class="cursor-pointer rounded-md px-2 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                                :disabled="form.processing"
                                @click="confirmingDelete = true"
                            >
                                Delete
                            </button>
                            <button
                                type="button"
                                class="ml-auto cursor-pointer rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-300"
                                :disabled="form.processing"
                                @click="close"
                            >
                                Cancel
                            </button>
                            <PrimaryButton type="submit" :disabled="form.processing"
                                >Save transaction</PrimaryButton
                            >
                        </div>
                    </form>
                    <ConfirmDialog
                        :open="confirmingDelete"
                        title="Delete transaction?"
                        message="This removes the transaction from your investment totals."
                        @cancel="confirmingDelete = false"
                        @confirm="remove"
                    />
                </DialogPanel>
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import PrimaryButton from '@/Components/Button/PrimaryButton.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import type { Direction, InvestmentTransaction } from '@/utils/investments';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    entry: InvestmentTransaction | null;
    categories: string[];
    defaultDate: string;
}>();
const emit = defineEmits<{ close: [] }>();
const confirmingDelete = ref(false);
const monthOnly = props.entry?.date === null;
const directions = [
    { value: 'deposit', label: 'Deposit' },
    { value: 'redemption', label: 'Redemption' },
] as const;

const form = useForm({
    direction: props.entry?.direction ?? ('deposit' as Direction),
    amount: props.entry ? String(props.entry.amount_paise / 100) : '',
    date: props.entry?.date ?? props.entry?.period ?? props.defaultDate,
    category: props.entry?.category.name ?? '',
    note: props.entry?.note ?? '',
});

function close() {
    if (!form.processing) {
        emit('close');
    }
}

function save() {
    const options = { preserveScroll: true, onSuccess: () => emit('close') };
    if (props.entry) {
        form.put('/investments/transactions/' + props.entry.id, options);
    } else {
        form.post('/investments/transactions', options);
    }
}

function remove() {
    if (props.entry && !form.processing) {
        form.delete('/investments/transactions/' + props.entry.id, {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
    }
}
</script>
