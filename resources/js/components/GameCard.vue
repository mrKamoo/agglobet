<template>
    <div class="border rounded-lg p-4 bg-white shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
        <!-- Match Header -->
        <div class="flex items-center justify-between mb-3">
            <!-- Home Team -->
            <div class="flex-1 flex flex-col items-center gap-1">
                <img
                    v-if="game.home_team.logo"
                    :src="game.home_team.logo"
                    :alt="game.home_team.name"
                    class="h-8 w-8 object-contain"
                    @error="handleImageError($event, 'home')"
                >
                <div v-else class="h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <span class="text-gray-400 text-xs font-bold">{{ game.home_team.short_name }}</span>
                </div>
                <p class="text-sm font-bold text-gray-900 line-clamp-1 text-center" :title="game.home_team.name">{{ game.home_team.short_name }}</p>

                <!-- Team Form Indicators -->
                <div v-if="game.home_team.form && game.home_team.form.length > 0" class="flex gap-1 mt-1">
                    <div
                        v-for="(result, index) in game.home_team.form"
                        :key="index"
                        :class="getFormClass(result)"
                        class="w-3 h-3 rounded-full"
                        :title="getFormTitle(result)"
                    ></div>
                </div>
            </div>

            <!-- Score / VS / Date -->
            <div class="px-2 flex-shrink-0">
                <div v-if="game.is_finished" class="text-center">
                    <div class="text-xl font-bold text-gray-900 whitespace-nowrap">
                        {{ game.home_score }} - {{ game.away_score }}
                    </div>
                    <p class="text-xs text-gray-400">Terminé</p>
                    <div v-if="game.user_prediction && game.user_prediction.points_earned !== null" class="mt-1">
                        <span
                            class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                            :class="getPointsBadgeClass(game.user_prediction.points_earned)"
                        >
                            {{ game.user_prediction.points_earned }} pts
                        </span>
                    </div>
                </div>
                <div v-else class="text-center">
                    <p class="text-lg text-gray-500 font-semibold">VS</p>
                    <p class="text-xs text-gray-600 font-medium whitespace-nowrap">{{ formatDate(game.match_date_formatted) }}</p>
                    <p class="text-xs text-gray-500">{{ formatTime(game.match_date_formatted) }}</p>
                    <div v-if="getTimeUntilMatch(game.match_date)" class="mt-1">
                        <span class="text-xs text-orange-600 font-medium whitespace-nowrap">
                            {{ getTimeUntilMatch(game.match_date) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Away Team -->
            <div class="flex-1 flex flex-col items-center gap-1">
                <img
                    v-if="game.away_team.logo"
                    :src="game.away_team.logo"
                    :alt="game.away_team.name"
                    class="h-8 w-8 object-contain"
                    @error="handleImageError($event, 'away')"
                >
                <div v-else class="h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <span class="text-gray-400 text-xs font-bold">{{ game.away_team.short_name }}</span>
                </div>
                <p class="text-sm font-bold text-gray-900 line-clamp-1 text-center" :title="game.away_team.name">{{ game.away_team.short_name }}</p>

                <!-- Team Form Indicators -->
                <div v-if="game.away_team.form && game.away_team.form.length > 0" class="flex gap-1 mt-1">
                    <div
                        v-for="(result, index) in game.away_team.form"
                        :key="index"
                        :class="getFormClass(result)"
                        class="w-3 h-3 rounded-full"
                        :title="getFormTitle(result)"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Prediction Form -->
        <div class="mt-auto">
            <prediction-form
                :game-id="game.id"
                :user-prediction="game.user_prediction"
                :can-predict="game.can_predict"
                :is-past="game.is_past"
                :is-finished="game.is_finished"
                @prediction-updated="handlePredictionUpdate"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import PredictionForm from './PredictionForm.vue';

const props = defineProps({
    game: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['prediction-updated']);

const handleImageError = (event, team) => {
    event.target.style.display = 'none';
};

const formatDate = (dateString) => {
    const parts = dateString.split(' ');
    return parts[0]; // Returns "dd/mm/yyyy"
};

const formatTime = (dateString) => {
    const parts = dateString.split(' ');
    return parts[1]; // Returns "HH:mm"
};

const getTimeUntilMatch = (dateString) => {
    const matchDate = new Date(props.game.match_date);
    const now = new Date();
    const diff = matchDate - now;

    if (diff < 0) return null;

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

    if (days > 0) {
        return `Dans ${days}j ${hours}h`;
    } else if (hours > 0) {
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        return `Dans ${hours}h ${minutes}min`;
    } else {
        const minutes = Math.floor(diff / (1000 * 60));
        return minutes > 0 ? `Dans ${minutes}min` : 'Bientôt';
    }
};

const getPointsBadgeClass = (points) => {
    if (points === 5) {
        return 'bg-green-100 text-green-800';
    } else if (points === 3) {
        return 'bg-blue-100 text-blue-800';
    } else if (points === 1) {
        return 'bg-yellow-100 text-yellow-800';
    } else {
        return 'bg-gray-100 text-gray-800';
    }
};

const getFormClass = (result) => {
    if (result === 'W') {
        return 'bg-green-500'; // Win - Green
    } else if (result === 'L') {
        return 'bg-red-500'; // Loss - Red
    } else {
        return 'bg-gray-500'; // Draw - Gray
    }
};

const getFormTitle = (result) => {
    if (result === 'W') {
        return 'Victoire';
    } else if (result === 'L') {
        return 'Défaite';
    } else {
        return 'Match nul';
    }
};

const handlePredictionUpdate = (data) => {
    emit('prediction-updated', data);
};
</script>
