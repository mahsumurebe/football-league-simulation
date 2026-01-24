<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
      Generate Fixtures
    </h2>

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-600 dark:text-gray-400">
            Current Teams: <span class="font-semibold">{{ teamCount }}</span>
          </p>
          <p
            v-if="teamCount < 2"
            class="text-sm text-red-600 dark:text-red-400 mt-1"
          >
            At least 2 teams required to generate fixtures
          </p>
        </div>
        <button
          @click="handleGenerate"
          :disabled="teamCount < 2 || loading"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="loading">Generating...</span>
          <span v-else>Generate Fixtures</span>
        </button>
      </div>

      <div
        v-if="error"
        class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded"
      >
        {{ error }}
      </div>

      <div
        v-if="successMessage"
        class="p-3 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded"
      >
        {{ successMessage }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useLeague } from '@/composables/useLeague';

const props = defineProps<{
  teamCount: number;
}>();

const emit = defineEmits<{
  fixturesGenerated: [];
}>();

const { generateFixtures, loading, error } = useLeague();
const successMessage = ref<string | null>(null);

const handleGenerate = async () => {
  if (props.teamCount < 2) {
    return;
  }

  try {
    successMessage.value = null;
    await generateFixtures();
    successMessage.value = 'Fixtures generated successfully!';
    emit('fixturesGenerated');
    
    setTimeout(() => {
      successMessage.value = null;
    }, 3000);
  } catch {
    // Error is handled by useLeague composable
  }
};
</script>
