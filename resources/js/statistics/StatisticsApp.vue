<script setup>
import { onMounted } from 'vue';
import StatisticsFeedback from './components/StatisticsFeedback.vue';
import StatisticsFilters from './components/StatisticsFilters.vue';
import StatisticsResults from './components/StatisticsResults.vue';
import { useStatistics } from './composables/useStatistics';

const props = defineProps({
    statisticsApi: {
        type: String,
        required: true,
    },
    modelsApi: {
        type: String,
        required: true,
    },
});

const {
    cars,
    dismissError,
    error,
    filters,
    hasLoaded,
    isLoadingModels,
    isLoadingResults,
    load,
    loadResults,
    models,
    resetFilters,
    totalVotes,
} = useStatistics(props);

onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Статистика голосования</h1>
            <p class="mt-2 text-slate-600">Результаты по импортированным автомобилям.</p>
        </div>

        <StatisticsFilters
            :models="models"
            :model="filters.model"
            :year-from="filters.yearFrom"
            :year-to="filters.yearTo"
            :is-loading-models="isLoadingModels"
            :is-loading-results="isLoadingResults"
            @update:model="filters.model = $event"
            @update:year-from="filters.yearFrom = $event"
            @update:year-to="filters.yearTo = $event"
            @submit="loadResults"
            @reset="resetFilters"
        />

        <StatisticsFeedback
            :error="error"
            :is-loading-models="isLoadingModels"
            :is-loading-results="isLoadingResults"
            :has-loaded="hasLoaded"
            @dismiss="dismissError"
        />

        <StatisticsResults
            v-if="hasLoaded"
            :cars="cars"
            :total-votes="totalVotes"
            :is-loading="isLoadingResults"
        />
    </div>
</template>
