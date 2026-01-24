<template>
    <div class="rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
        <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            League Table
        </h2>

        <div class="-mx-6 overflow-x-auto px-6">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead
                    class="bg-gray-100 text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-300"
                >
                    <tr>
                        <th class="px-3 py-3 sm:px-4">Pos</th>
                        <th class="px-3 py-3 sm:px-4">Team</th>
                        <th class="px-2 py-3 text-center sm:px-3">P</th>
                        <th class="px-2 py-3 text-center sm:px-3">W</th>
                        <th class="px-2 py-3 text-center sm:px-3">D</th>
                        <th class="px-2 py-3 text-center sm:px-3">L</th>
                        <th class="px-2 py-3 text-center sm:px-3">GF</th>
                        <th class="px-2 py-3 text-center sm:px-3">GA</th>
                        <th class="px-2 py-3 text-center sm:px-3">GD</th>
                        <th class="px-3 py-3 text-center font-bold sm:px-4">
                            Pts
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(standing, index) in table"
                        :key="standing.id"
                        class="border-b transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                    >
                        <td class="px-3 py-3 font-semibold sm:px-4">
                            {{ index + 1 }}
                        </td>
                        <td
                            class="px-3 py-3 font-medium text-gray-900 sm:px-4 dark:text-white"
                        >
                            {{ standing.team.name }}
                        </td>
                        <td class="px-2 py-3 text-center sm:px-3">
                            {{ standing.played }}
                        </td>
                        <td class="px-2 py-3 text-center sm:px-3">
                            {{ standing.won }}
                        </td>
                        <td class="px-2 py-3 text-center sm:px-3">
                            {{ standing.drawn }}
                        </td>
                        <td class="px-2 py-3 text-center sm:px-3">
                            {{ standing.lost }}
                        </td>
                        <td class="px-2 py-3 text-center sm:px-3">
                            {{ standing.goals_for }}
                        </td>
                        <td class="px-2 py-3 text-center sm:px-3">
                            {{ standing.goals_against }}
                        </td>
                        <td
                            class="px-2 py-3 text-center sm:px-3"
                            :class="{
                                'text-green-600 dark:text-green-400':
                                    standing.goal_difference > 0,
                                'text-red-600 dark:text-red-400':
                                    standing.goal_difference < 0,
                            }"
                        >
                            {{ standing.goal_difference > 0 ? '+' : ''
                            }}{{ standing.goal_difference }}
                        </td>
                        <td
                            class="px-3 py-3 text-center font-bold text-gray-900 sm:px-4 dark:text-white"
                        >
                            {{ standing.points }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
interface LeagueStanding {
    id: number;
    team_id: number;
    played: number;
    won: number;
    drawn: number;
    lost: number;
    goals_for: number;
    goals_against: number;
    goal_difference: number;
    points: number;
    team: {
        id: number;
        name: string;
        power: number;
    };
}

defineProps<{
    table: LeagueStanding[];
}>();
</script>
