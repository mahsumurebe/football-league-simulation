<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
      League Table
    </h2>

    <div class="overflow-x-auto -mx-6 px-6">
      <table class="w-full text-sm text-left min-w-[600px]">
        <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-100 dark:bg-gray-700">
          <tr>
            <th class="px-3 sm:px-4 py-3">Pos</th>
            <th class="px-3 sm:px-4 py-3">Team</th>
            <th class="px-2 sm:px-3 py-3 text-center">P</th>
            <th class="px-2 sm:px-3 py-3 text-center">W</th>
            <th class="px-2 sm:px-3 py-3 text-center">D</th>
            <th class="px-2 sm:px-3 py-3 text-center">L</th>
            <th class="px-2 sm:px-3 py-3 text-center">GF</th>
            <th class="px-2 sm:px-3 py-3 text-center">GA</th>
            <th class="px-2 sm:px-3 py-3 text-center">GD</th>
            <th class="px-3 sm:px-4 py-3 text-center font-bold">Pts</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(standing, index) in table"
            :key="standing.id"
            class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
          >
            <td class="px-3 sm:px-4 py-3 font-semibold">{{ index + 1 }}</td>
            <td class="px-3 sm:px-4 py-3 font-medium text-gray-900 dark:text-white">
              {{ standing.team.name }}
            </td>
            <td class="px-2 sm:px-3 py-3 text-center">{{ standing.played }}</td>
            <td class="px-2 sm:px-3 py-3 text-center">{{ standing.won }}</td>
            <td class="px-2 sm:px-3 py-3 text-center">{{ standing.drawn }}</td>
            <td class="px-2 sm:px-3 py-3 text-center">{{ standing.lost }}</td>
            <td class="px-2 sm:px-3 py-3 text-center">{{ standing.goals_for }}</td>
            <td class="px-2 sm:px-3 py-3 text-center">{{ standing.goals_against }}</td>
            <td
              class="px-2 sm:px-3 py-3 text-center"
              :class="{
                'text-green-600 dark:text-green-400': standing.goal_difference > 0,
                'text-red-600 dark:text-red-400': standing.goal_difference < 0,
              }"
            >
              {{ standing.goal_difference > 0 ? '+' : '' }}{{ standing.goal_difference }}
            </td>
            <td class="px-3 sm:px-4 py-3 text-center font-bold text-gray-900 dark:text-white">
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
