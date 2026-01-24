<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <Navigation />

    <div class="py-8 px-4">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Fixtures
          </h2>
          <p class="text-gray-600 dark:text-gray-400">
            View all league fixtures grouped by week
          </p>
        </div>

        <!-- Error Message -->
        <div
          v-if="error"
          class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg"
        >
          {{ error }}
        </div>

        <!-- Fixtures Grid (Weeks side by side) -->
        <div v-if="!hasFixtures" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-md">
          <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">
            No fixtures generated yet.
          </p>
          <p class="text-gray-400 dark:text-gray-500">
            Please generate fixtures first from the Generate Fixtures page.
          </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div
            v-for="weekData in matches"
            :key="weekData.week"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 border border-gray-200 dark:border-gray-700"
          >
            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
              Week {{ weekData.week }}
            </h3>
            <div class="space-y-2">
              <div
                v-for="match in weekData.matches"
                :key="match.id"
                class="p-2 bg-gray-50 dark:bg-gray-700/50 rounded text-sm"
              >
                <div class="flex flex-col space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white text-xs">
                      {{ match.home_team }}
                    </span>
                    <span
                      v-if="match.played"
                      class="font-semibold text-gray-900 dark:text-white"
                    >
                      {{ match.home_score }}
                    </span>
                    <span v-else class="text-gray-400 dark:text-gray-500 text-xs">
                      -
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white text-xs">
                      {{ match.away_team }}
                    </span>
                    <span
                      v-if="match.played"
                      class="font-semibold text-gray-900 dark:text-white"
                    >
                      {{ match.away_score }}
                    </span>
                    <span v-else class="text-gray-400 dark:text-gray-500 text-xs">
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
            class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-lg"
          >
            Start Simulation
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import Navigation from '@/components/Navigation.vue';
import { useLeague } from '@/composables/useLeague';

const {
  matches,
  error,
  hasFixtures,
  loadMatches,
} = useLeague();

const handleStartSimulation = () => {
  router.visit('/simulation');
};

onMounted(async () => {
  await loadMatches();
});
</script>
