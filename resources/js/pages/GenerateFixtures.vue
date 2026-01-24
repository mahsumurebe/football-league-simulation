<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <Navigation />

    <div class="py-8 px-4">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Tournament Teams
          </h2>
        </div>

        <!-- Error Message -->
        <div
          v-if="error"
          class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg"
        >
          {{ error }}
        </div>

        <!-- Teams List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <div v-if="teams.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No teams found. Please add teams first.</p>
          </div>
          <div v-else class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <div
                v-for="team in teams"
                :key="team.id"
                class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600"
              >
                <div class="font-semibold text-gray-900 dark:text-white">
                  {{ team.name }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  Power: {{ team.power }}
                </div>
              </div>
            </div>

            <!-- Generate Fixtures Button -->
            <div class="flex justify-center pt-4">
              <button
                @click="handleGenerate"
                :disabled="teams.length < 2 || loading"
                class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors font-medium text-lg"
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
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useLeague } from '@/composables/useLeague';
import Navigation from '@/components/Navigation.vue';

const {
  teams,
  error,
  loading,
  loadTeams,
  generateFixtures,
} = useLeague();

const handleGenerate = async () => {
  if (teams.length < 2) {
    return;
  }

  try {
    await generateFixtures();
    router.visit('/fixtures');
  } catch (err) {
  }
};

onMounted(async () => {
  await loadTeams();
});
</script>
