<script setup>
defineProps({
    cars: {
        type: Array,
        required: true,
    },
    totalVotes: {
        type: Number,
        required: true,
    },
    isLoading: {
        type: Boolean,
        required: true,
    },
});

function carDetails(car) {
    return [car.engine, car.transmission, car.color].filter(Boolean).join(' · ');
}

function odometer(car) {
    if (car.odometer === null || car.odometer === undefined) {
        return 'Пробег не указан';
    }

    const units = car.units ? ` ${car.units}` : '';

    return `${car.odometer.toLocaleString('ru-RU')}${units}`;
}
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-sky-950 p-5 text-white">
        <span class="text-sky-100">Всего голосов по выбранным фильтрам</span>
        <strong class="text-2xl">{{ totalVotes.toLocaleString('ru-RU') }}</strong>
    </div>

    <p v-if="isLoading" class="text-sm text-slate-600" aria-live="polite">
        Обновляем результаты.
    </p>

    <div v-if="cars.length === 0" class="rounded-xl bg-white p-8 text-center text-slate-600 shadow-sm">
        По выбранным фильтрам автомобили не найдены.
    </div>

    <div v-else>
        <div class="hidden overflow-x-auto rounded-2xl bg-white shadow-sm md:block">
            <table class="min-w-full divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50 text-sm text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Фото</th>
                        <th class="px-4 py-3">Автомобиль</th>
                        <th class="px-4 py-3">Данные</th>
                        <th class="px-4 py-3 text-right">Голоса</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="car in cars" :key="car.id">
                        <td class="px-4 py-3">
                            <img
                                v-if="car.photo"
                                class="h-20 w-28 rounded-lg object-cover"
                                :src="car.photo.url"
                                :alt="car.model_label"
                            >
                            <span v-else class="text-sm text-slate-500">Фото отсутствует</span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ car.model_label }}
                            <span class="mt-1 block font-normal text-slate-600">{{ car.year }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ odometer(car) }}
                            <span v-if="carDetails(car)" class="mt-1 block text-sm text-slate-500">
                                {{ carDetails(car) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-lg font-bold text-sky-800">
                            {{ car.votes_count }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 md:hidden">
            <article v-for="car in cars" :key="car.id" class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <img
                    v-if="car.photo"
                    class="h-48 w-full object-cover"
                    :src="car.photo.url"
                    :alt="car.model_label"
                >
                <div v-else class="flex h-32 items-center justify-center bg-slate-100 text-sm text-slate-500">
                    Фото отсутствует
                </div>
                <div class="space-y-2 p-5">
                    <h2 class="text-lg font-bold">{{ car.model_label }}</h2>
                    <p class="text-slate-600">{{ car.year }} · {{ odometer(car) }}</p>
                    <p v-if="carDetails(car)" class="text-sm text-slate-500">{{ carDetails(car) }}</p>
                    <p class="pt-2 text-lg font-bold text-sky-800">Голосов: {{ car.votes_count }}</p>
                </div>
            </article>
        </div>
    </div>
</template>
