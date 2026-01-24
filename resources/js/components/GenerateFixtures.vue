<template>
    <div class="rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
        <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            Generate Fixtures
        </h2>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400">
                        Current Teams:
                        <span class="font-semibold">{{ teamCount }}</span>
                    </p>
                    <p
                        v-if="teamCount < 2"
                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                        At least 2 teams required to generate fixtures
                    </p>
                </div>
                <button
                    @click="handleGenerate"
                    :disabled="teamCount < 2 || loading"
                    class="rounded-lg bg-blue-600 px-6 py-2 text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                >
                    <span v-if="loading">Generating...</span>
                    <span v-else>Generate Fixtures</span>
                </button>
            </div>

            <div
                v-if="error"
                class="rounded border border-red-400 bg-red-100 p-3 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300"
            >
                {{ error }}
            </div>

            <div
                v-if="successMessage"
                class="rounded border border-green-400 bg-green-100 p-3 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300"
            >
                {{ successMessage }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useLeague } from '@/composables/useLeague';
import { ref } from 'vue';

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
