import { onBeforeUnmount, reactive, ref } from 'vue';
import { fetchStatistics, fetchStatisticsModels } from '../api/statisticsApi';

export function useStatistics({ statisticsApi, modelsApi }) {
    const models = ref([]);
    const cars = ref([]);
    const totalVotes = ref(0);
    const filters = reactive({
        model: '',
        yearFrom: '',
        yearTo: '',
    });
    const isLoadingModels = ref(false);
    const isLoadingResults = ref(false);
    const error = ref('');
    const hasLoaded = ref(false);
    let resultsController = null;
    let requestSequence = 0;

    function validateFilters() {
        const { yearFrom, yearTo } = filters;

        if ((yearFrom && !/^\d+$/.test(yearFrom)) || (yearTo && !/^\d+$/.test(yearTo))) {
            error.value = 'Год выпуска должен быть целым числом.';

            return false;
        }

        if (yearFrom && yearTo && Number(yearFrom) > Number(yearTo)) {
            error.value = 'Год «от» не может быть больше года «до».';

            return false;
        }

        return true;
    }

    async function loadResults() {
        if (!statisticsApi) {
            error.value = 'Не настроен адрес статистики.';

            return;
        }

        if (!validateFilters()) {
            return;
        }

        error.value = '';
        resultsController?.abort();
        resultsController = new AbortController();
        const sequence = ++requestSequence;
        isLoadingResults.value = true;

        try {
            const payload = await fetchStatistics(statisticsApi, filters, resultsController.signal);

            if (sequence !== requestSequence) {
                return;
            }

            cars.value = payload.data;
            totalVotes.value = payload.meta.total_votes;
            hasLoaded.value = true;
        } catch (requestError) {
            if (requestError.name !== 'AbortError' && sequence === requestSequence) {
                error.value = requestError.message;
            }
        } finally {
            if (sequence === requestSequence) {
                isLoadingResults.value = false;
            }
        }
    }

    async function load() {
        if (!modelsApi) {
            error.value = 'Не настроен адрес списка моделей.';

            return;
        }

        isLoadingModels.value = true;
        error.value = '';

        try {
            models.value = await fetchStatisticsModels(modelsApi);
            await loadResults();
        } catch (requestError) {
            error.value = requestError.message;
        } finally {
            isLoadingModels.value = false;
        }
    }

    function resetFilters() {
        filters.model = '';
        filters.yearFrom = '';
        filters.yearTo = '';
        loadResults();
    }

    function dismissError() {
        error.value = '';
    }

    onBeforeUnmount(() => resultsController?.abort());

    return {
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
    };
}
