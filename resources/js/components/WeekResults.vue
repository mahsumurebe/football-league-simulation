<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
      Week Results
    </h2>

    <div class="mb-4">
      <label
        for="week-select"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Select Week:
      </label>
      <select
        id="week-select"
        v-model="selectedWeek"
        @change="handleWeekChange"
        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option
          v-for="week in availableWeeks"
          :key="week"
          :value="week"
        >
          Week {{ week }}
        </option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500 dark:text-gray-400">
      Loading...
    </div>

    <div
      v-else-if="weekMatches.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      No matches found for this week.
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="match in weekMatches"
        :key="match.id"
        class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
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
          class="ml-4 font-semibold text-lg text-gray-900 dark:text-white"
        >
          {{ match.home_score }} - {{ match.away_score }}
        </div>
        <div v-else class="ml-4 text-gray-400 dark:text-gray-500 text-sm">
          Not played
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface Match {
  id: number;
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
  currentWeek: number | null;
}>();

const emit = defineEmits<{
  weekChanged: [week: number];
}>();

const selectedWeek = ref<number>(
  props.currentWeek || props.matches[0]?.week || 1
);
const loading = ref(false);

const availableWeeks = computed(() => {
  return props.matches.map((w) => w.week).sort((a, b) => a - b);
});

const weekMatches = computed(() => {
  const weekData = props.matches.find((w) => w.week === selectedWeek.value);
  return weekData?.matches || [];
});

const handleWeekChange = () => {
  emit('weekChanged', selectedWeek.value);
};

watch(
  () => props.currentWeek,
  (newWeek) => {
    if (newWeek !== null) {
      selectedWeek.value = newWeek;
    }
  }
);
</script>
