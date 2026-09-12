<script setup>
const props = defineProps({
    models: {
        type: Array,
        required: true,
    },
    model: {
        type: String,
        required: true,
    },
    yearFrom: {
        type: String,
        required: true,
    },
    yearTo: {
        type: String,
        required: true,
    },
    isLoadingModels: {
        type: Boolean,
        required: true,
    },
    isLoadingResults: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits([
    'update:model',
    'update:yearFrom',
    'update:yearTo',
    'submit',
    'reset',
]);

const currentYear = new Date().getFullYear();

function dateValue(year) {
    return year ? `${year}-01-01` : '';
}

function updateYear(event, eventName) {
    emit(`update:${eventName}`, event.target.value.slice(0, 4));
}

function submit() {
    emit('submit');
}
</script>

<template>
    <form class="rounded-2xl bg-white p-5 shadow-sm sm:p-6" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-3">
            <label class="grid min-w-0 gap-2 text-sm font-semibold text-slate-800">
                Модель
                <select
                    :value="props.model"
                    class="min-w-0 rounded-lg border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-600 focus:ring-sky-600"
                    :disabled="props.isLoadingModels || props.isLoadingResults"
                    @change="emit('update:model', $event.target.value)"
                >
                    <option value="">Все модели</option>
                    <option v-for="item in props.models" :key="item.key" :value="item.key">
                        {{ item.label }}
                    </option>
                </select>
            </label>

            <label class="grid min-w-0 gap-2 text-sm font-semibold text-slate-800">
                Год от
                <input
                    class="min-w-0 rounded-lg border-slate-300 text-slate-950 shadow-sm focus:border-sky-600 focus:ring-sky-600"
                    type="date"
                    min="1886-01-01"
                    :max="`${currentYear}-12-31`"
                    :value="dateValue(props.yearFrom)"
                    :disabled="props.isLoadingResults"
                    @input="updateYear($event, 'yearFrom')"
                >
            </label>

            <label class="grid min-w-0 gap-2 text-sm font-semibold text-slate-800">
                Год до
                <input
                    class="min-w-0 rounded-lg border-slate-300 text-slate-950 shadow-sm focus:border-sky-600 focus:ring-sky-600"
                    type="date"
                    min="1886-01-01"
                    :max="`${currentYear}-12-31`"
                    :value="dateValue(props.yearTo)"
                    :disabled="props.isLoadingResults"
                    @input="updateYear($event, 'yearTo')"
                >
            </label>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <button
                class="cursor-pointer rounded-lg bg-sky-700 px-4 py-2.5 font-semibold text-white hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                type="submit"
                :disabled="props.isLoadingModels || props.isLoadingResults"
            >
                Применить
            </button>
            <button
                class="cursor-pointer rounded-lg border border-slate-300 px-4 py-2.5 font-semibold text-slate-800 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-700 disabled:cursor-not-allowed"
                type="button"
                :disabled="props.isLoadingResults"
                @click="emit('reset')"
            >
                Сбросить
            </button>
        </div>
    </form>
</template>
