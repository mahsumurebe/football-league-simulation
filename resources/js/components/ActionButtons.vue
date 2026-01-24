<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
      Actions
    </h2>

    <div class="flex flex-col sm:flex-row flex-wrap gap-4">
      <button
        @click="handlePlayAll"
        :disabled="loading || allGamesPlayed"
        class="flex-1 sm:flex-none px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors font-medium"
      >
        <span v-if="loading && actionType === 'playAll'">Playing...</span>
        <span v-else>Play All Weeks</span>
      </button>

      <button
        @click="handlePlayNext"
        :disabled="loading || !canPlayNextWeek"
        class="flex-1 sm:flex-none px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors font-medium"
      >
        <span v-if="loading && actionType === 'playNext'">Playing...</span>
        <span v-else>Play Next Week</span>
      </button>

      <button
        @click="handleReset"
        :disabled="loading"
        class="flex-1 sm:flex-none px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors font-medium"
      >
        <span v-if="loading && actionType === 'reset'">Resetting...</span>
        <span v-else>Reset Data</span>
      </button>
    </div>

    <!-- Reset Confirmation Modal -->
    <div
      v-if="showResetModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="showResetModal = false"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4"
        @click.stop
      >
        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
          Confirm Reset
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Are you sure you want to reset all results and fixtures? This action
          cannot be undone.
        </p>
        <div class="flex gap-4 justify-end">
          <button
            @click="showResetModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="confirmReset"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Reset
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
  loading: boolean;
  canPlayNextWeek: boolean;
  allGamesPlayed: boolean;
}>();

const emit = defineEmits<{
  playAll: [];
  playNext: [];
  reset: [];
}>();

const showResetModal = ref(false);
const actionType = ref<'playAll' | 'playNext' | 'reset' | null>(null);

const handlePlayAll = async () => {
  actionType.value = 'playAll';
  emit('playAll');
  actionType.value = null;
};

const handlePlayNext = async () => {
  actionType.value = 'playNext';
  emit('playNext');
  actionType.value = null;
};

const handleReset = () => {
  showResetModal.value = true;
};

const confirmReset = async () => {
  showResetModal.value = false;
  actionType.value = 'reset';
  emit('reset');
  actionType.value = null;
};
</script>
