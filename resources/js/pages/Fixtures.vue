<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <Navigation />

        <div class="px-4 py-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Page Header -->
                <div class="mb-8 text-center">
                    <h2
                        class="mb-2 text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        Fixtures
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        View all league fixtures grouped by week
                    </p>
                </div>

                <!-- Error Message -->
                <div
                    v-if="error"
                    class="rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300"
                >
                    {{ error }}
                </div>

                <!-- Fixtures Grid (Weeks side by side) -->
                <div
                    v-if="!hasFixtures"
                    class="rounded-lg bg-white py-12 text-center shadow-md dark:bg-gray-800"
                >
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">
                        No fixtures generated yet.
                    </p>
                    <p class="text-gray-400 dark:text-gray-500">
                        Please generate fixtures first from the Generate
                        Fixtures page.
                    </p>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <div
                        v-for="weekData in matches"
                        :key="weekData.week"
                        class="rounded-lg border border-gray-200 bg-white p-4 shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3
                            class="mb-3 border-b border-gray-200 pb-2 text-lg font-semibold text-gray-900 dark:border-gray-700 dark:text-white"
                        >
                            Week {{ weekData.week }}
                        </h3>
                        <div class="space-y-2">
                            <div
                                v-for="match in weekData.matches"
                                :key="match.id"
                                class="rounded bg-gray-50 p-2 text-sm dark:bg-gray-700/50"
                            >
                                <div class="flex flex-col space-y-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-xs font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ match.home_team }}
                                        </span>
                                        <span
                                            v-if="match.played"
                                            class="font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ match.home_score }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-gray-400 dark:text-gray-500"
                                        >
                                            -
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-xs font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ match.away_team }}
                                        </span>
                                        <span
                                            v-if="match.played"
                                            class="font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ match.away_score }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-gray-400 dark:text-gray-500"
                                        >
                                            -
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Start Simulation Button -->
                <div v-if="hasFixtures" class="flex justify-center pt-6">
                    <button
                        @click="handleStartSimulation"
                        class="rounded-lg bg-green-600 px-8 py-3 text-lg font-medium text-white transition-colors hover:bg-green-700"
                    >
                        Start Simulation
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Navigation from '@/components/Navigation.vue';
import { useLeague } from '@/composables/useLeague';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const { matches, error, hasFixtures, loadMatches } = useLeague();

const handleStartSimulation = () => {
    router.visit('/simulation');
};

onMounted(async () => {
    await loadMatches();
});
</script>
