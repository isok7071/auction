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
    const pagination = reactive({
        currentPage: 1,
        lastPage: 1,
        perPage: 24,
        total: 0,
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

    async function loadResults(page = 1) {
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
            const payload = await fetchStatistics(
                statisticsApi,
                filters,
                page,
                pagination.perPage,
                resultsController.signal,
            );

            if (sequence !== requestSequence) {
                return;
            }

            cars.value = payload.data;
            totalVotes.value = payload.meta.total_votes;
            pagination.currentPage = payload.meta.current_page;
            pagination.lastPage = payload.meta.last_page;
            pagination.perPage = payload.meta.per_page;
            pagination.total = payload.meta.total;
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

    async function loadModels() {
        if (!modelsApi) {
            error.value = 'Не настроен адрес списка моделей.';

            return false;
        }

        isLoadingModels.value = true;
        error.value = '';

        try {
            models.value = await fetchStatisticsModels(modelsApi);

            return true;
        } catch (requestError) {
            error.value = requestError.message;

            return false;
        } finally {
            isLoadingModels.value = false;
        }
    }

    async function load() {
        if (await loadModels()) {
            await loadResults();
        }
    }

    function resetFilters() {
        filters.model = '';
        filters.yearFrom = '';
        filters.yearTo = '';
        loadResults(1);
    }

    function dismissError() {
        error.value = '';
    }

    onBeforeUnmount(() => {
        resultsController?.abort();
    });

    return {
        cars,
        dismissError,
        error,
        filters,
        hasLoaded,
        isLoadingModels,
        isLoadingResults,
        load,
        loadModels,
        loadResults,
        models,
        pagination,
        resetFilters,
        totalVotes,
    };
}
