<template>
    <nav class="bg-white shadow-md dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <Link
                        href="/generate-fixtures"
                        class="text-xl font-bold text-gray-900 transition-colors hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                    >
                        Football League Simulation
                    </Link>
                </div>
                <div class="hidden space-x-4 md:flex">
                    <Link
                        href="/generate-fixtures"
                        :class="[
                            'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                            url === '/generate-fixtures'
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                        ]"
                    >
                        Generate Fixtures
                    </Link>
                    <Link
                        href="/fixtures"
                        :class="[
                            'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                            url === '/fixtures'
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                        ]"
                    >
                        Fixtures
                    </Link>
                    <Link
                        href="/simulation"
                        :class="[
                            'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                            url === '/simulation'
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                        ]"
                    >
                        Simulation
                    </Link>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                v-if="!mobileMenuOpen"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="space-y-2 pb-4 md:hidden">
                <Link
                    href="/generate-fixtures"
                    :class="[
                        'block rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        url === '/generate-fixtures'
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="mobileMenuOpen = false"
                >
                    Generate Fixtures
                </Link>
                <Link
                    href="/fixtures"
                    :class="[
                        'block rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        url === '/fixtures'
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="mobileMenuOpen = false"
                >
                    Fixtures
                </Link>
                <Link
                    href="/simulation"
                    :class="[
                        'block rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        url === '/simulation'
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="mobileMenuOpen = false"
                >
                    Simulation
                </Link>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const { url } = usePage();
const mobileMenuOpen = ref(false);
</script>
