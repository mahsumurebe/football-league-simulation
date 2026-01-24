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
                        Tournament Teams
                    </h2>
                </div>

                <!-- Error Message -->
                <div
                    v-if="error"
                    class="rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300"
                >
                    {{ error }}
                </div>

                <!-- Teams List -->
                <div class="rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
                    <div
                        v-if="teams.length === 0"
                        class="py-8 text-center text-gray-500 dark:text-gray-400"
                    >
                        <p>No teams found. Please add teams first.</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                        >
                            <div
                                v-for="team in teams"
                                :key="team.id"
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700/50"
                            >
                                <div
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ team.name }}
                                </div>
                                <div
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Power: {{ team.power }}
                                </div>
                            </div>
                        </div>

                        <!-- Generate Fixtures Button -->
                        <div class="flex justify-center pt-4">
                            <button
                                @click="handleGenerate"
                                :disabled="teams.length < 2 || loading"
                                class="rounded-lg bg-blue-600 px-8 py-3 text-lg font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                            >
                                <span v-if="loading">Generating...</span>
                                <span v-else>Generate Fixtures</span>
                            </button>
                        </div>

                        <div
                            v-if="teams.length < 2"
                            class="text-center text-sm text-red-600 dark:text-red-400"
                        >
                            At least 2 teams required to generate fixtures
                        </div>
                    </div>
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

const { teams, error, loading, loadTeams, generateFixtures } = useLeague();

const handleGenerate = async () => {
    if (teams.length < 2) {
        return;
    }

    try {
        await generateFixtures();
        router.visit('/fixtures');
    } catch {
        // Error is handled by useLeague composable
    }
};

onMounted(async () => {
    await loadTeams();
});
</script>
