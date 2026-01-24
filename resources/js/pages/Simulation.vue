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
                        Simulation
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Simulate matches, view results, and championship
                        predictions
                    </p>
                </div>

                <!-- Error Message -->
                <div
                    v-if="error"
                    class="rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300"
                >
                    {{ error }}
                </div>

                <div
                    v-if="hasFixtures"
                    :class="[
                        'grid gap-6 grid-cols-1'
                    ]"
                >
                    <!-- League Table -->
                    <div>
                        <LeagueTable
                            v-if="leagueTable.length > 0"
                            :table="leagueTable"
                        />
                    </div>
                </div>

                <!-- Simulation Section - Side by Side -->
                <div
                    v-if="hasFixtures"
                    :class="[
                        'grid gap-6',
                        showPredictions
                            ? 'grid-cols-1 lg:grid-cols-2'
                            : 'grid-cols-1 lg:grid-cols-1',
                    ]"
                >
                    <!-- Week Results -->
                    <div>
                        <WeekResults
                            :matches="matches"
                            :current-week="currentWeek.last_played_week"
                            @week-changed="handleWeekChanged"
                        />
                    </div>

                    <!-- Championship Predictions -->
                    <div v-if="showPredictions">
                        <ChampionshipPredictions
                            :predictions="predictions"
                            :show="showPredictions"
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div v-if="hasFixtures">
                    <ActionButtons
                        :loading="loading"
                        :can-play-next-week="canPlayNextWeek"
                        :all-games-played="allGamesPlayed"
                        @play-all="handlePlayAll"
                        @play-next="handlePlayNext"
                        @reset="handleReset"
                    />
                </div>

                <!-- Empty State -->
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
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useLeague } from '@/composables/useLeague';
import Navigation from '@/components/Navigation.vue';
import LeagueTable from '@/components/LeagueTable.vue';
import WeekResults from '@/components/WeekResults.vue';
import ChampionshipPredictions from '@/components/ChampionshipPredictions.vue';
import ActionButtons from '@/components/ActionButtons.vue';

const {
    matches,
    leagueTable,
    predictions,
    currentWeek,
    loading,
    error,
    hasFixtures,
    canPlayNextWeek,
    allGamesPlayed,
    showPredictions,
    loadMatches,
    loadLeagueTable,
    loadPredictions,
    loadCurrentWeek,
    simulateNextWeek,
    simulateAll,
    resetLeague,
    refreshAll,
} = useLeague();

const handleWeekChanged = (week: number) => {
};

const handlePlayAll = async () => {
    try {
        await simulateAll();
        await refreshAll();
    } catch (err) {
    }
};

const handlePlayNext = async () => {
    try {
        await simulateNextWeek();
        await refreshAll();
    } catch (err) {
    }
};

const handleReset = async () => {
    try {
        await resetLeague();
        await refreshAll();
        router.visit('/generate-fixtures');
    } catch (err) {
    }
};

onMounted(async () => {
    await refreshAll();
});
</script>
