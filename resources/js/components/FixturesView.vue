<template>
    <div class="rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
        <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            Fixtures
        </h2>

        <div
            v-if="!hasFixtures"
            class="py-8 text-center text-gray-500 dark:text-gray-400"
        >
            <p>No fixtures generated yet. Please generate fixtures first.</p>
        </div>

        <div v-else class="space-y-6">
            <div
                v-for="weekData in matches"
                :key="weekData.week"
                class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
            >
                <h3
                    class="mb-3 text-lg font-semibold text-gray-900 dark:text-white"
                >
                    Week {{ weekData.week }}
                </h3>
                <div class="space-y-2">
                    <div
                        v-for="match in weekData.matches"
                        :key="match.id"
                        class="flex items-center justify-between rounded bg-gray-50 p-2 dark:bg-gray-700/50"
                    >
                        <div class="flex flex-1 items-center justify-between">
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ match.home_team }}
                            </span>
                            <span class="mx-4 text-gray-500 dark:text-gray-400"
                                >vs</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ match.away_team }}
                            </span>
                        </div>
                        <div
                            v-if="match.played"
                            class="ml-4 font-semibold text-gray-900 dark:text-white"
                        >
                            {{ match.home_score }} - {{ match.away_score }}
                        </div>
                        <div
                            v-else
                            class="ml-4 text-sm text-gray-400 dark:text-gray-500"
                        >
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
