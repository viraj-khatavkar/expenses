<template>
    <div>
        <TransitionRoot as="template" :show="sidebarOpen">
            <Dialog class="relative z-50 lg:hidden" @close="sidebarOpen = false">
                <TransitionChild
                    as="template"
                    enter="transition-opacity ease-linear duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="transition-opacity ease-linear duration-300"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-gray-900/80" />
                </TransitionChild>

                <div class="fixed inset-0 flex">
                    <TransitionChild
                        as="template"
                        enter="transition ease-in-out duration-300 transform"
                        enter-from="-translate-x-full"
                        enter-to="translate-x-0"
                        leave="transition ease-in-out duration-300 transform"
                        leave-from="translate-x-0"
                        leave-to="-translate-x-full"
                    >
                        <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
                            <TransitionChild
                                as="template"
                                enter="ease-in-out duration-300"
                                enter-from="opacity-0"
                                enter-to="opacity-100"
                                leave="ease-in-out duration-300"
                                leave-from="opacity-100"
                                leave-to="opacity-0"
                            >
                                <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                                    <button
                                        type="button"
                                        class="-m-2.5 cursor-pointer p-2.5"
                                        @click="sidebarOpen = false"
                                    >
                                        <span class="sr-only">Close sidebar</span>
                                        <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                                    </button>
                                </div>
                            </TransitionChild>

                            <div
                                class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4"
                            >
                                <div
                                    class="flex h-16 shrink-0 items-center text-base font-semibold text-gray-900"
                                >
                                    Expenses Tracker
                                </div>
                                <nav class="flex flex-1 flex-col">
                                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                        <li>
                                            <ul role="list" class="-mx-2 space-y-1">
                                                <li
                                                    v-for="item in mainNavigation"
                                                    :key="item.name"
                                                >
                                                    <Link
                                                        :href="item.href"
                                                        @click="sidebarOpen = false"
                                                        :class="navLinkClasses(item.current)"
                                                    >
                                                        <component
                                                            :is="item.icon"
                                                            :class="navIconClasses(item.current)"
                                                            aria-hidden="true"
                                                        />
                                                        {{ item.name }}
                                                    </Link>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <div
                                                class="text-xs/6 font-semibold text-gray-400 uppercase"
                                            >
                                                Manage
                                            </div>
                                            <ul role="list" class="-mx-2 mt-2 space-y-1">
                                                <li
                                                    v-for="item in manageNavigation"
                                                    :key="item.name"
                                                >
                                                    <Link
                                                        :href="item.href"
                                                        @click="sidebarOpen = false"
                                                        :class="navLinkClasses(item.current)"
                                                    >
                                                        <component
                                                            :is="item.icon"
                                                            :class="navIconClasses(item.current)"
                                                            aria-hidden="true"
                                                        />
                                                        {{ item.name }}
                                                    </Link>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="-mx-2 mt-auto space-y-1 pt-4">
                                            <Link
                                                href="/account/password"
                                                @click="sidebarOpen = false"
                                                :class="
                                                    navLinkClasses(
                                                        page.url.startsWith('/account'),
                                                    )
                                                "
                                            >
                                                <KeyIcon
                                                    :class="
                                                        navIconClasses(
                                                            page.url.startsWith('/account'),
                                                        )
                                                    "
                                                    aria-hidden="true"
                                                />
                                                Change Password
                                            </Link>
                                            <Link
                                                href="/logout"
                                                method="post"
                                                :class="navLinkClasses(false)"
                                            >
                                                <ArrowRightStartOnRectangleIcon
                                                    :class="navIconClasses(false)"
                                                    aria-hidden="true"
                                                />
                                                Sign Out
                                            </Link>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>

        <div class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-64 lg:flex-col">
            <div
                class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4"
            >
                <div
                    class="flex h-16 shrink-0 items-center text-base font-semibold text-gray-900"
                >
                    Expenses Tracker
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li v-for="item in mainNavigation" :key="item.name">
                                    <Link
                                        :href="item.href"
                                        :class="navLinkClasses(item.current)"
                                    >
                                        <component
                                            :is="item.icon"
                                            :class="navIconClasses(item.current)"
                                            aria-hidden="true"
                                        />
                                        {{ item.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="text-xs/6 font-semibold text-gray-400 uppercase">
                                Manage
                            </div>
                            <ul role="list" class="-mx-2 mt-2 space-y-1">
                                <li v-for="item in manageNavigation" :key="item.name">
                                    <Link
                                        :href="item.href"
                                        :class="navLinkClasses(item.current)"
                                    >
                                        <component
                                            :is="item.icon"
                                            :class="navIconClasses(item.current)"
                                            aria-hidden="true"
                                        />
                                        {{ item.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li class="-mx-2 mt-auto space-y-1 pt-4">
                            <Link
                                href="/account/password"
                                :class="navLinkClasses(page.url.startsWith('/account'))"
                            >
                                <KeyIcon
                                    :class="navIconClasses(page.url.startsWith('/account'))"
                                    aria-hidden="true"
                                />
                                Change Password
                            </Link>
                            <Link
                                href="/logout"
                                method="post"
                                :class="navLinkClasses(false)"
                            >
                                <ArrowRightStartOnRectangleIcon
                                    :class="navIconClasses(false)"
                                    aria-hidden="true"
                                />
                                Sign Out
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div
            class="sticky top-0 z-30 flex items-center gap-x-6 border-b border-gray-200 bg-white px-4 py-4 shadow-xs sm:px-6 lg:hidden"
        >
            <button
                type="button"
                class="-m-2.5 cursor-pointer p-2.5 text-gray-700"
                @click="sidebarOpen = true"
            >
                <span class="sr-only">Open sidebar</span>
                <Bars3Icon class="size-6" aria-hidden="true" />
            </button>
            <div class="flex-1 text-sm font-semibold text-gray-900">
                {{ currentSectionName }}
            </div>
        </div>

        <div class="lg:pl-64">
            <main class="py-8 pb-28 lg:py-10 lg:pb-28">
                <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <SuccessAlert v-if="$page.props.flash.success" class="mb-8">
                        {{ $page.props.flash.success }}
                    </SuccessAlert>

                    <slot />
                </div>
            </main>

            <Link
                v-if="showFab"
                data-test="add-expense-button"
                :href="fab.href"
                :aria-label="fab.label"
                class="fixed right-5 bottom-5 z-40 flex size-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:right-8 sm:bottom-8"
            >
                <PlusIcon class="size-6" aria-hidden="true" />
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import {
    ArrowPathIcon,
    AdjustmentsHorizontalIcon,
    ArrowRightStartOnRectangleIcon,
    BanknotesIcon,
    Bars3Icon,
    ChartBarIcon,
    CreditCardIcon,
    HomeIcon,
    InboxStackIcon,
    KeyIcon,
    PlusIcon,
    TagIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SuccessAlert from './Components/Alerts/SuccessAlert.vue';

const page = usePage();
const sidebarOpen = ref(false);

const mainNavigation = computed(() => [
    { name: 'Home', href: '/', icon: HomeIcon, current: page.url === '/' },
    {
        name: 'Expenses',
        href: '/expenses',
        icon: CreditCardIcon,
        current: page.url.startsWith('/expenses'),
    },
    {
        name: 'Income',
        href: '/income',
        icon: BanknotesIcon,
        current: page.url.startsWith('/income'),
    },
    {
        name: 'Subscriptions',
        href: '/subscriptions',
        icon: ArrowPathIcon,
        current: page.url.startsWith('/subscriptions'),
    },
    {
        name: 'Reports',
        href: '/reports',
        icon: ChartBarIcon,
        current: page.url.startsWith('/reports'),
    },
    {
        name: 'Explore',
        href: '/explore',
        icon: AdjustmentsHorizontalIcon,
        current: page.url.startsWith('/explore'),
    },
]);

const manageNavigation = computed(() => [
    {
        name: 'Categories',
        href: '/categories',
        icon: TagIcon,
        current: page.url.startsWith('/categories'),
    },
    {
        name: 'Sources',
        href: '/sources',
        icon: InboxStackIcon,
        current: page.url.startsWith('/sources'),
    },
]);

const currentSectionName = computed(() => {
    const active = [...mainNavigation.value, ...manageNavigation.value].find(
        (item) => item.current,
    );

    if (active) {
        return active.name;
    }

    return page.url.startsWith('/account') ? 'Account' : 'Expenses Tracker';
});

const fab = computed(() => {
    const url = page.url;

    if (url.startsWith('/income')) {
        return { href: '/income/create', label: 'Add income' };
    }

    if (url.startsWith('/subscriptions')) {
        return { href: '/subscriptions/create', label: 'Add subscription' };
    }

    if (url.startsWith('/categories')) {
        return { href: '/categories/create', label: 'Add category' };
    }

    if (url.startsWith('/sources')) {
        return { href: '/sources/create', label: 'Add source' };
    }

    return { href: '/expenses/create', label: 'Add expense' };
});

const showFab = computed(() => {
    const path = page.url.split('?')[0];

    return (
        !path.endsWith('/create') && !path.endsWith('/edit') && !path.startsWith('/account')
    );
});

function navLinkClasses(current: boolean): string {
    return [
        current
            ? 'bg-gray-50 text-indigo-600'
            : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600',
        'group flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold',
    ].join(' ');
}

function navIconClasses(current: boolean): string {
    return [
        current ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600',
        'size-6 shrink-0',
    ].join(' ');
}
</script>
