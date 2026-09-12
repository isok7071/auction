const fallbackError = 'Не удалось загрузить данные. Проверьте подключение и повторите попытку.';

async function requestJson(url, options = {}) {
    let response;

    try {
        response = await fetch(url, {
            headers: { Accept: 'application/json' },
            ...options,
        });
    } catch (requestError) {
        if (requestError.name === 'AbortError') {
            throw requestError;
        }

        throw new Error(fallbackError, { cause: requestError });
    }

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const firstError = Object.values(payload.errors ?? {}).flat()[0];

        throw new Error(typeof firstError === 'string'
            ? firstError
            : (payload.message ?? fallbackError));
    }

    return payload;
}

export async function fetchStatisticsModels(url) {
    const payload = await requestJson(url);

    return payload.data;
}

export async function fetchStatistics(url, filters, signal) {
    const params = new URLSearchParams();
    const model = filters.model.trim();

    if (model) {
        params.set('model', model);
    }

    if (filters.yearFrom) {
        params.set('year_from', filters.yearFrom);
    }

    if (filters.yearTo) {
        params.set('year_to', filters.yearTo);
    }

    const query = params.toString();

    return requestJson(`${url}${query ? `?${query}` : ''}`, { signal });
}
