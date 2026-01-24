<template>
    <div class="rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
        <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            Week Results
        </h2>

        <div class="mb-4">
            <label
                for="week-select"
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Select Week:
            </label>
            <select
                id="week-select"
                v-model="selectedWeek"
                @change="handleWeekChange"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
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

        <div
            v-if="loading"
            class="py-8 text-center text-gray-500 dark:text-gray-400"
        >
            Loading...
        </div>

        <div
            v-else-if="weekMatches.length === 0"
            class="py-8 text-center text-gray-500 dark:text-gray-400"
        >
            No matches found for this week.
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="match in weekMatches"
                :key="match.id"
                class="flex items-center justify-between rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50"
            >
                <div class="flex flex-1 items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ match.home_team }}
                    </span>
                    <span class="mx-4 text-gray-500 dark:text-gray-400"
                        >vs</span
                    >
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ match.away_team }}
                    </span>
                </div>
                <div
                    v-if="match.played"
                    class="ml-4 text-lg font-semibold text-gray-900 dark:text-white"
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
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';

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
    props.currentWeek || props.matches[0]?.week || 1,
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
    },
);
</script>
