<script setup>
defineProps({
    error: {
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
    hasLoaded: {
        type: Boolean,
        required: true,
    },
});

defineEmits(['dismiss']);
</script>

<template>
    <div
        v-if="error"
        class="flex items-start justify-between gap-4 rounded-xl bg-rose-50 p-4 text-rose-950"
        role="alert"
    >
        <p>{{ error }}</p>
        <button class="font-semibold underline" type="button" @click="$emit('dismiss')">
            Закрыть
        </button>
    </div>

    <p v-if="isLoadingModels" class="rounded-xl bg-white p-5 text-slate-600 shadow-sm" aria-live="polite">
        Загружаем фильтры.
    </p>
    <p
        v-else-if="isLoadingResults && !hasLoaded"
        class="rounded-xl bg-white p-5 text-slate-600 shadow-sm"
        aria-live="polite"
    >
        Загружаем статистику.
    </p>
</template>
