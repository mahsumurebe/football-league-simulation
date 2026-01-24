<template>
    <div v-if="show" class="rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
        <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            Championship Predictions
        </h2>

        <div
            v-if="predictions.length === 0"
            class="py-8 text-center text-gray-500 dark:text-gray-400"
        >
            <p>Predictions will be available after week 4.</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="prediction in sortedPredictions"
                :key="prediction.team_id"
                class="space-y-2"
            >
                <div class="mb-1 flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ prediction.team_name }}
                    </span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ prediction.probability.toFixed(2) }}%
                    </span>
                </div>
                <div
                    class="h-4 w-full rounded-full bg-gray-200 dark:bg-gray-700"
                >
                    <div
                        class="h-4 rounded-full bg-blue-600 transition-all duration-300 dark:bg-blue-500"
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
    return [...props.predictions].sort((a, b) => b.probability - a.probability);
});
</script>
