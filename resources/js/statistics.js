import { createApp } from 'vue';
import StatisticsApp from './statistics/StatisticsApp.vue';

const root = document.querySelector('#statistics-page');

if (root) {
    createApp(StatisticsApp, {
        statisticsApi: root.dataset.statisticsApi,
        modelsApi: root.dataset.statisticsModelsApi,
    }).mount(root);
}
