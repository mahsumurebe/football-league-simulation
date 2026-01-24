<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
      Fixtures
    </h2>

    <div v-if="!hasFixtures" class="text-center py-8 text-gray-500 dark:text-gray-400">
      <p>No fixtures generated yet. Please generate fixtures first.</p>
    </div>

    <div v-else class="space-y-6">
      <div
        v-for="weekData in matches"
        :key="weekData.week"
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
      >
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">
          Week {{ weekData.week }}
        </h3>
        <div class="space-y-2">
          <div
            v-for="match in weekData.matches"
            :key="match.id"
            class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700/50 rounded"
          >
            <div class="flex-1 flex items-center justify-between">
              <span class="font-medium text-gray-900 dark:text-white">
                {{ match.home_team }}
              </span>
              <span class="mx-4 text-gray-500 dark:text-gray-400">vs</span>
              <span class="font-medium text-gray-900 dark:text-white">
                {{ match.away_team }}
              </span>
            </div>
            <div
              v-if="match.played"
              class="ml-4 font-semibold text-gray-900 dark:text-white"
            >
              {{ match.home_score }} - {{ match.away_score }}
            </div>
            <div v-else class="ml-4 text-gray-400 dark:text-gray-500 text-sm">
              Not played
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Match {
  id: number;
  home_team_id: number;
  away_team_id: number;
  home_team: string;
  away_team: string;
  home_score: number | null;
  away_score: number | null;
  played: boolean;
  week: number;
}

interface WeekData {
  week: number;
  matches: Match[];
}

const props = defineProps<{
  matches: WeekData[];
}>();

const hasFixtures = computed(() => props.matches.length > 0);
</script>
