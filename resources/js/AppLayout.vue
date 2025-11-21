<template>
    <Head title="Expenses Tracker" />
    <div class="min-h-full">
        <Disclosure
            as="nav"
            class="border-b border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900"
            v-slot="{ open }"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">Expenses Tracker</div>
                        <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                            <Link
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    item.current
                                        ? 'border-indigo-600 text-gray-900 dark:border-indigo-500 dark:text-white'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-white/20 dark:hover:text-gray-200',
                                    'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium',
                                ]"
                                :aria-current="item.current ? 'page' : undefined"
                            >
                                {{ item.name }}
                            </Link>
                            <Link
                                href="/logout"
                                method="post"
                                class="inline-flex cursor-pointer items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-white/20 dark:hover:text-gray-200"
                            >
                                Sign Out
                            </Link>
                        </div>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">
                        <!-- Mobile menu button -->
                        <DisclosureButton
                            class="relative inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-600 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white dark:focus:outline-indigo-500"
                        >
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <Bars3Icon v-if="!open" class="block size-6" aria-hidden="true" />
                            <XMarkIcon v-else class="block size-6" aria-hidden="true" />
                        </DisclosureButton>
                    </div>
                </div>
            </div>

            <DisclosurePanel class="sm:hidden" v-slot="{ close }">
                <div class="space-y-1 pt-2 pb-3">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        as="a"
                        :href="item.href"
                        @click="close"
                        :class="[
                            item.current
                                ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-600/10 dark:text-indigo-300'
                                : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200',
                            'block border-l-4 py-2 pr-4 pl-3 text-base font-medium',
                        ]"
                        :aria-current="item.current ? 'page' : undefined"
                        >{{ item.name }}
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        class="block cursor-pointer border-l-4 border-transparent py-2 pr-4 pl-3 text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200"
                    >
                        Sign Out 1
                    </Link>
                </div>
            </DisclosurePanel>
        </Disclosure>

        <div class="lg:py-10">
            <main>
                <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <SuccessAlert v-if="$page.props.flash.success" class="mb-8">
                        {{ $page.props.flash.success }}
                    </SuccessAlert>

                    <slot />

                    <Link
                        data-test="add-expense-button"
                        href="/expenses/create"
                        class="fixed right-[max(2rem,calc((100vw-80rem)/2+2rem))] bottom-8 h-14 w-14 justify-around rounded-full bg-indigo-600 p-4 text-center text-white hover:bg-indigo-500"
                    >
                        <PlusIcon />
                    </Link>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { User } from '@/types/app/Models/User';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { Bars3Icon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SuccessAlert from './Components/Alerts/SuccessAlert.vue';

const page = usePage();
const user: User = computed(() => page.props.auth.user);

const navigation = computed(() => {
    return [
        { name: 'Home', href: '/', current: page.url === '/' },
        { name: 'Categories', href: '/categories', current: page.url.startsWith('/categories') },
        { name: 'Expenses', href: '/expenses', current: page.url.startsWith('/expenses') },
        { name: 'Reports', href: '/reports', current: page.url.startsWith('/reports') },
    ];
});

const userNavigation = [{ name: 'Sign out', href: '/logout' }];
</script>
