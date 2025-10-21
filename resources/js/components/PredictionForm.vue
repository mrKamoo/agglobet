<template>
    <div v-if="canPredict" class="mt-4 border-t pt-4">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 shadow-sm relative">
            <!-- Auto-save Status Indicator -->
            <div class="absolute top-4 right-4 flex items-center gap-2">
                <transition name="fade">
                    <div v-if="saveStatus === 'saving'" class="flex items-center gap-2 text-xs text-blue-600">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="font-medium">Enregistrement...</span>
                    </div>
                </transition>
                <transition name="fade">
                    <div v-if="saveStatus === 'saved'" class="flex items-center gap-2 text-xs text-green-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">Enregistré</span>
                    </div>
                </transition>
                <transition name="fade">
                    <div v-if="saveStatus === 'error'" class="flex items-center gap-2 text-xs text-red-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">Erreur</span>
                    </div>
                </transition>
            </div>

            <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ hasPrediction ? 'Mon pronostic' : 'Pronostic' }}
            </h3>

            <div class="flex flex-col items-center gap-2">
                <!-- Labels Row -->
                <div class="flex items-center justify-center gap-3 w-full">
                    <label class="text-xs font-medium text-gray-600 uppercase tracking-wide text-center w-16">
                        Dom.
                    </label>
                    <span class="w-6"></span>
                    <label class="text-xs font-medium text-gray-600 uppercase tracking-wide text-center w-16">
                        Ext.
                    </label>
                </div>

                <!-- Inputs Row -->
                <div class="flex items-center justify-center gap-3">
                    <!-- Home Score Input -->
                    <div class="relative">
                        <input
                            v-model.number="homeScore"
                            type="number"
                            min="0"
                            max="20"
                            class="w-16 h-12 text-2xl font-bold text-center border-2 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all disabled:bg-gray-100 disabled:cursor-not-allowed"
                            :class="getInputClass()"
                            :disabled="saveStatus === 'saving'"
                            @focus="inputFocused = true"
                            @blur="inputFocused = false"
                        >
                    </div>

                    <!-- Separator -->
                    <span class="text-xl font-bold text-gray-400">-</span>

                    <!-- Away Score Input -->
                    <div class="relative">
                        <input
                            v-model.number="awayScore"
                            type="number"
                            min="0"
                            max="20"
                            class="w-16 h-12 text-2xl font-bold text-center border-2 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all disabled:bg-gray-100 disabled:cursor-not-allowed"
                            :class="getInputClass()"
                            :disabled="saveStatus === 'saving'"
                            @focus="inputFocused = true"
                            @blur="inputFocused = false"
                        >
                    </div>
                </div>
            </div>

            <!-- Status Messages -->
            <transition name="fade">
                <div v-if="error" class="mt-3 flex items-center justify-center gap-2 text-xs text-red-700 bg-red-100 px-3 py-1.5 rounded-lg">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ error }}</span>
                </div>
            </transition>

            <p class="text-center text-xs text-gray-500 mt-2 italic">
                Auto-save
            </p>
        </div>
    </div>
    <div v-else-if="isPast && !isFinished" class="mt-4 border-t pt-4">
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center">
            <div class="flex items-center justify-center gap-2 text-orange-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="text-sm font-medium">Fermé</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    gameId: {
        type: Number,
        required: true
    },
    userPrediction: {
        type: Object,
        default: null
    },
    canPredict: {
        type: Boolean,
        default: false
    },
    isPast: {
        type: Boolean,
        default: false
    },
    isFinished: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['prediction-updated']);

const homeScore = ref(props.userPrediction?.home_score ?? 0);
const awayScore = ref(props.userPrediction?.away_score ?? 0);
const saveStatus = ref(''); // 'saving', 'saved', 'error', ''
const error = ref('');
const hasPrediction = ref(!!props.userPrediction);
const inputFocused = ref(false);
let saveTimeout = null;

// Watch for changes in userPrediction prop
watch(() => props.userPrediction, (newPrediction) => {
    if (newPrediction) {
        homeScore.value = newPrediction.home_score;
        awayScore.value = newPrediction.away_score;
        hasPrediction.value = true;
    }
}, { immediate: true });

// Auto-save when scores change (with debounce)
watch([homeScore, awayScore], () => {
    // Clear previous timeout
    if (saveTimeout) {
        clearTimeout(saveTimeout);
    }

    // Reset error
    error.value = '';

    // Debounce: wait 800ms after user stops typing
    saveTimeout = setTimeout(() => {
        autoSavePrediction();
    }, 800);
});

const autoSavePrediction = async () => {
    // Validate scores are numbers and within range
    if (typeof homeScore.value !== 'number' || typeof awayScore.value !== 'number') {
        return;
    }

    if (homeScore.value < 0 || homeScore.value > 20 || awayScore.value < 0 || awayScore.value > 20) {
        return;
    }

    saveStatus.value = 'saving';
    error.value = '';

    try {
        const response = await axios.post(`/games/${props.gameId}/predictions`, {
            home_score: homeScore.value,
            away_score: awayScore.value
        });

        hasPrediction.value = true;
        saveStatus.value = 'saved';

        // Hide "saved" status after 2 seconds
        setTimeout(() => {
            if (saveStatus.value === 'saved') {
                saveStatus.value = '';
            }
        }, 2000);

        // Emit event to parent component
        emit('prediction-updated', {
            gameId: props.gameId,
            prediction: {
                home_score: homeScore.value,
                away_score: awayScore.value
            }
        });
    } catch (err) {
        console.error('Error saving prediction:', err);
        saveStatus.value = 'error';
        error.value = err.response?.data?.message || 'Erreur lors de l\'enregistrement automatique';

        // Hide error status after 3 seconds
        setTimeout(() => {
            if (saveStatus.value === 'error') {
                saveStatus.value = '';
            }
        }, 3000);
    }
};

const getInputClass = () => {
    const classes = [];

    if (saveStatus.value === 'saving') {
        classes.push('border-blue-400 bg-blue-50');
    } else if (saveStatus.value === 'saved' || hasPrediction.value) {
        classes.push('border-green-400 bg-green-50');
    } else if (saveStatus.value === 'error') {
        classes.push('border-red-400 bg-red-50');
    } else {
        classes.push('border-gray-300 bg-white');
    }

    return classes.join(' ');
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
