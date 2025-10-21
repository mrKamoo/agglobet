<template>
    <div class="space-y-6">
        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Matchday Filter -->
                <div>
                    <label for="matchday-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        Journée
                    </label>
                    <select
                        id="matchday-filter"
                        v-model="filters.matchday"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    >
                        <option :value="null">Toutes les journées</option>
                        <option v-for="day in availableMatchdays" :key="day" :value="day">
                            Journée {{ day }}
                        </option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        Statut
                    </label>
                    <select
                        id="status-filter"
                        v-model="filters.status"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    >
                        <option value="">Tous les matchs</option>
                        <option value="upcoming">À venir</option>
                        <option value="finished">Terminés</option>
                    </select>
                </div>

                <!-- Search Filter -->
                <div>
                    <label for="search-filter" class="block text-sm font-medium text-gray-700 mb-2">
                        Rechercher une équipe
                    </label>
                    <div class="relative">
                        <input
                            id="search-filter"
                            v-model="filters.search"
                            type="text"
                            placeholder="Nom de l'équipe..."
                            class="mt-1 block w-full pl-10 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span v-if="!isLoading">{{ filteredGamesCount }} match(s) trouvé(s)</span>
                </div>
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                    Réinitialiser les filtres
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600">Chargement des matchs...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-4 text-red-800 font-medium">{{ error }}</p>
            <button
                @click="loadGames"
                class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            >
                Réessayer
            </button>
        </div>

        <!-- Empty State -->
        <div v-else-if="games.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="mt-4 text-gray-600">Aucun match trouvé avec ces critères.</p>
        </div>

        <!-- Games Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <transition-group name="fade">
                <game-card
                    v-for="game in games"
                    :key="game.id"
                    :game="game"
                    @prediction-updated="handlePredictionUpdate"
                />
            </transition-group>
        </div>

        <!-- Success Notification -->
        <transition name="slide-up">
            <div
                v-if="showSuccessNotification"
                class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Pronostic mis à jour avec succès !</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import GameCard from './GameCard.vue';

const games = ref([]);
const availableMatchdays = ref([]);
const season = ref(null);
const isLoading = ref(true);
const error = ref('');
const showSuccessNotification = ref(false);

const filters = ref({
    matchday: null,
    status: '',
    search: ''
});

const filteredGamesCount = computed(() => games.value.length);

const hasActiveFilters = computed(() => {
    return filters.value.matchday !== null ||
           filters.value.status !== '' ||
           filters.value.search !== '';
});

// Watch filters and reload games
watch(filters, () => {
    loadGames();
}, { deep: true });

const loadGames = async () => {
    isLoading.value = true;
    error.value = '';

    try {
        const params = {};

        if (filters.value.matchday !== null) {
            params.matchday = filters.value.matchday;
        }
        if (filters.value.status) {
            params.status = filters.value.status;
        }
        if (filters.value.search) {
            params.search = filters.value.search;
        }

        const response = await axios.get('/api/games', { params });

        games.value = response.data.games;
        availableMatchdays.value = response.data.matchdays;
        season.value = response.data.season;
    } catch (err) {
        console.error('Error loading games:', err);
        error.value = 'Erreur lors du chargement des matchs. Veuillez réessayer.';
    } finally {
        isLoading.value = false;
    }
};

const clearFilters = () => {
    filters.value = {
        matchday: null,
        status: '',
        search: ''
    };
};

const handlePredictionUpdate = (data) => {
    // Update the game's prediction in the list
    const game = games.value.find(g => g.id === data.gameId);
    if (game) {
        game.user_prediction = data.prediction;
    }

    // Show success notification
    showSuccessNotification.value = true;
    setTimeout(() => {
        showSuccessNotification.value = false;
    }, 3000);
};

onMounted(() => {
    loadGames();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active, .slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from, .slide-up-leave-to {
    transform: translateY(100px);
    opacity: 0;
}
</style>
