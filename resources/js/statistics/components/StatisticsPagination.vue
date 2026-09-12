<script setup>
defineProps({
    pagination: {
        type: Object,
        required: true,
    },
    isLoading: {
        type: Boolean,
        required: true,
    },
});

defineEmits(['change']);
</script>

<template>
    <nav
        v-if="pagination.lastPage > 1"
        class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4 shadow-sm"
        aria-label="Пагинация статистики"
    >
        <p class="text-sm text-slate-600">
            Страница {{ pagination.currentPage }} из {{ pagination.lastPage }} · автомобилей: {{ pagination.total }}
        </p>
        <div class="flex gap-2">
            <button
                class="cursor-pointer rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-800 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                type="button"
                :disabled="isLoading || pagination.currentPage === 1"
                @click="$emit('change', pagination.currentPage - 1)"
            >
                Назад
            </button>
            <button
                class="cursor-pointer rounded-lg bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                type="button"
                :disabled="isLoading || pagination.currentPage === pagination.lastPage"
                @click="$emit('change', pagination.currentPage + 1)"
            >
                Далее
            </button>
        </div>
    </nav>
</template>
