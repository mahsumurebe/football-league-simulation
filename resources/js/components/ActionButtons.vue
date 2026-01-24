<template>
    <div class="rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
        <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            Actions
        </h2>

        <div class="flex flex-col flex-wrap gap-4 sm:flex-row">
            <button
                @click="handlePlayAll"
                :disabled="loading || allGamesPlayed"
                class="flex-1 rounded-lg bg-green-600 px-6 py-2 font-medium text-white transition-colors hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-400 sm:flex-none"
            >
                <span v-if="loading && actionType === 'playAll'"
                    >Playing...</span
                >
                <span v-else>Play All Weeks</span>
            </button>

            <button
                @click="handlePlayNext"
                :disabled="loading || !canPlayNextWeek"
                class="flex-1 rounded-lg bg-blue-600 px-6 py-2 font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400 sm:flex-none"
            >
                <span v-if="loading && actionType === 'playNext'"
                    >Playing...</span
                >
                <span v-else>Play Next Week</span>
            </button>

            <button
                @click="handleReset"
                :disabled="loading"
                class="flex-1 rounded-lg bg-red-600 px-6 py-2 font-medium text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-gray-400 sm:flex-none"
            >
                <span v-if="loading && actionType === 'reset'"
                    >Resetting...</span
                >
                <span v-else>Reset Data</span>
            </button>
        </div>

        <!-- Reset Confirmation Modal -->
        <div
            v-if="showResetModal"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black"
            @click="showResetModal = false"
        >
            <div
                class="mx-4 w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-800"
                @click.stop
            >
                <h3
                    class="mb-4 text-xl font-bold text-gray-900 dark:text-white"
                >
                    Confirm Reset
                </h3>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Are you sure you want to reset all results and fixtures?
                    This action cannot be undone.
                </p>
                <div class="flex justify-end gap-4">
                    <button
                        @click="showResetModal = false"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-gray-800 transition-colors hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmReset"
                        class="rounded-lg bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700"
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
