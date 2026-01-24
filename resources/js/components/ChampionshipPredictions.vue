<template>
  <div
    v-if="show"
    class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6"
  >
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
      Championship Predictions
    </h2>

    <div v-if="predictions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      <p>Predictions will be available after week 4.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="prediction in sortedPredictions"
        :key="prediction.team_id"
        class="space-y-2"
      >
        <div class="flex items-center justify-between mb-1">
          <span class="font-medium text-gray-900 dark:text-white">
            {{ prediction.team_name }}
          </span>
          <span class="font-semibold text-gray-900 dark:text-white">
            {{ prediction.probability.toFixed(2) }}%
          </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
          <div
            class="bg-blue-600 dark:bg-blue-500 h-4 rounded-full transition-all duration-300"
            :style="{ width: `${prediction.probability}%` }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Prediction {
  team_id: number;
  team_name: string;
  probability: number;
}

const props = defineProps<{
  predictions: Prediction[];
  show: boolean;
}>();

const sortedPredictions = computed(() => {
  return [...props.predictions].sort(
    (a, b) => b.probability - a.probability
  );
});
</script>
