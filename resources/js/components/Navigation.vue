<template>
  <nav class="bg-white dark:bg-gray-800 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <Link href="/generate-fixtures" class="text-xl font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            Football League Simulation
          </Link>
        </div>
        <div class="hidden md:flex space-x-4">
          <Link
            href="/generate-fixtures"
            :class="[
              'px-3 py-2 rounded-md text-sm font-medium transition-colors',
              url === '/generate-fixtures'
                ? 'bg-blue-600 text-white'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
            ]"
          >
            Generate Fixtures
          </Link>
          <Link
            href="/fixtures"
            :class="[
              'px-3 py-2 rounded-md text-sm font-medium transition-colors',
              url === '/fixtures'
                ? 'bg-blue-600 text-white'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
            ]"
          >
            Fixtures
          </Link>
          <Link
            href="/simulation"
            :class="[
              'px-3 py-2 rounded-md text-sm font-medium transition-colors',
              url === '/simulation'
                ? 'bg-blue-600 text-white'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
            ]"
          >
            Simulation
          </Link>
        </div>
        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                v-if="!mobileMenuOpen"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>
      <!-- Mobile menu -->
      <div v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-2">
        <Link
          href="/generate-fixtures"
          :class="[
            'block px-3 py-2 rounded-md text-sm font-medium transition-colors',
            url === '/generate-fixtures'
              ? 'bg-blue-600 text-white'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
          ]"
          @click="mobileMenuOpen = false"
        >
          Generate Fixtures
        </Link>
        <Link
          href="/fixtures"
          :class="[
            'block px-3 py-2 rounded-md text-sm font-medium transition-colors',
            url === '/fixtures'
              ? 'bg-blue-600 text-white'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
          ]"
          @click="mobileMenuOpen = false"
        >
          Fixtures
        </Link>
        <Link
          href="/simulation"
          :class="[
            'block px-3 py-2 rounded-md text-sm font-medium transition-colors',
            url === '/simulation'
              ? 'bg-blue-600 text-white'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
          ]"
          @click="mobileMenuOpen = false"
        >
          Simulation
        </Link>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const { url } = usePage();
const mobileMenuOpen = ref(false);
</script>
