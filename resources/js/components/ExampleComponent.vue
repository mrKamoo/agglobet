<template>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ title }}</h2>
        <p class="text-gray-600 mb-4">{{ message }}</p>

        <div class="flex items-center gap-4">
            <button
                @click="increment"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors"
            >
                Incrémenter
            </button>
            <span class="text-lg font-semibold text-gray-900">Compteur: {{ counter }}</span>
            <button
                @click="reset"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
            >
                Réinitialiser
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const title = ref('Composant Vue.js 3 - Exemple');
const message = ref('Bienvenue dans Agglobet avec Vue.js ! Ce composant démontre l\'intégration réussie.');
const counter = ref(0);

const increment = () => {
    counter.value++;
};

const reset = () => {
    counter.value = 0;
};
</script>

<style scoped>
/* Styles spécifiques au composant si nécessaire */
</style>
