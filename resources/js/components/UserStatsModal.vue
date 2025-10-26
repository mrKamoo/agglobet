<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" @click="close">
        <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="relative inline-block w-full max-w-3xl overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl my-8" @click.stop>
                <!-- Header - Always visible -->
                <div class="px-6 py-4" style="background: linear-gradient(to right, rgb(37, 99, 235), rgb(79, 70, 229));">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">
                            {{ stats?.user?.name || 'Statistiques du joueur' }}
                        </h3>
                        <button @click="close" class="text-white hover:text-gray-200 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Loading state -->
                <div v-if="loading" class="px-6 py-12 text-center">
                    <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="mt-4 text-gray-600">Chargement des statistiques...</p>
                </div>

                <div v-else-if="stats" class="px-6 py-4 max-h-[calc(100vh-250px)] overflow-y-auto">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-3 rounded-lg">
                            <div class="text-xs font-medium text-blue-600">Total Points</div>
                            <div class="text-2xl font-bold text-blue-900 mt-1">{{ stats.stats.total_points }}</div>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 p-3 rounded-lg">
                            <div class="text-xs font-medium text-green-600">Taux de réussite</div>
                            <div class="text-2xl font-bold text-green-900 mt-1">{{ stats.stats.success_rate }}%</div>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-3 rounded-lg">
                            <div class="text-xs font-medium text-purple-600">Moyenne/match</div>
                            <div class="text-2xl font-bold text-purple-900 mt-1">{{ stats.stats.avg_points }}</div>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-3 rounded-lg">
                            <div class="text-xs font-medium text-orange-600">Pronostics</div>
                            <div class="text-2xl font-bold text-orange-900 mt-1">{{ stats.stats.predictions_count }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="font-semibold text-gray-900 mb-3 text-sm">Répartition des points</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700">Scores exacts (5 pts)</span>
                                    </div>
                                    <span class="font-bold text-gray-900">{{ stats.stats.exact_scores }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" :style="{ width: getPercentage(stats.stats.exact_scores) + '%' }"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700">Bonnes différences (3 pts)</span>
                                    </div>
                                    <span class="font-bold text-gray-900">{{ stats.stats.correct_differences }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" :style="{ width: getPercentage(stats.stats.correct_differences) + '%' }"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700">Bons vainqueurs (1 pt)</span>
                                    </div>
                                    <span class="font-bold text-gray-900">{{ stats.stats.correct_winners }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" :style="{ width: getPercentage(stats.stats.correct_winners) + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="font-semibold text-gray-900 mb-3 text-sm">Séries et records</h4>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-white rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <div class="text-xl">🔥</div>
                                        <div>
                                            <div class="text-xs text-gray-600">Série en cours</div>
                                            <div class="font-bold">{{ stats.stats.current_streak }} matchs</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-white rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <div class="text-xl">⭐</div>
                                        <div>
                                            <div class="text-xs text-gray-600">Meilleure série</div>
                                            <div class="font-bold">{{ stats.stats.best_streak }} matchs</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="stats.stats.exact_scores >= 5" class="flex items-center justify-between p-2 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg border border-yellow-300">
                                    <div class="flex items-center gap-2">
                                        <div class="text-xl">🎯</div>
                                        <div>
                                            <div class="text-xs text-yellow-800 font-medium">Badge: Sniper</div>
                                            <div class="text-xs text-yellow-700">5+ scores exacts</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="stats.stats.current_streak >= 5" class="flex items-center justify-between p-2 bg-gradient-to-r from-red-50 to-red-100 rounded-lg border border-red-300">
                                    <div class="flex items-center gap-2">
                                        <div class="text-xl">🔥</div>
                                        <div>
                                            <div class="text-xs text-red-800 font-medium">Badge: En feu</div>
                                            <div class="text-xs text-red-700">Série de {{ stats.stats.current_streak }} matchs</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <h4 class="font-semibold text-gray-900 mb-3 text-sm">Évolution des points par journée</h4>
                        <div class="bg-white p-3 rounded-lg">
                            <canvas ref="chartCanvas" height="80"></canvas>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-3 text-sm">Derniers pronostics</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            <div v-for="prediction in stats.recent_predictions" :key="prediction.match_date"
                                class="flex items-center justify-between p-2 bg-white rounded-lg text-sm">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ prediction.home_team }} vs {{ prediction.away_team }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ prediction.match_date }}</div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-sm">
                                        <span class="text-gray-500">Prono:</span>
                                        <span class="font-medium ml-1">{{ prediction.prediction }}</span>
                                    </div>
                                    <div class="text-sm">
                                        <span class="text-gray-500">Résultat:</span>
                                        <span class="font-medium ml-1">{{ prediction.result }}</span>
                                    </div>
                                    <div class="px-3 py-1 rounded-full text-sm font-bold" :class="getPointsClass(prediction.points)">
                                        {{ prediction.points }} pts
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 bg-gray-50 flex justify-end">
                    <button @click="close" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Title, Tooltip, Legend } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Title, Tooltip, Legend);

const props = defineProps({
    userId: Number,
    isOpen: Boolean,
});

const emit = defineEmits(['close']);

const stats = ref(null);
const loading = ref(false);
const chartCanvas = ref(null);
let chartInstance = null;

watch(() => props.userId, async (newUserId) => {
    if (newUserId && props.isOpen) {
        await loadStats(newUserId);
    }
});

watch(() => props.isOpen, async (isOpen) => {
    if (isOpen && props.userId) {
        await loadStats(props.userId);
    } else if (!isOpen && chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});

async function loadStats(userId) {
    loading.value = true;
    try {
        const response = await fetch(`/api/users/${userId}/stats`);
        stats.value = await response.json();
        await nextTick();
        createChart();
    } catch (error) {
        console.error('Failed to load user stats:', error);
    } finally {
        loading.value = false;
    }
}

function createChart() {
    if (!chartCanvas.value || !stats.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    const ctx = chartCanvas.value.getContext('2d');
    const evolution = stats.value.points_evolution || [];

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: evolution.map(e => `J${e.matchday}`),
            datasets: [{
                label: 'Points par journée',
                data: evolution.map(e => e.points),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.y} points`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function close() {
    emit('close');
}

function getPercentage(value) {
    if (!stats.value) return 0;
    const total = stats.value.stats.predictions_count;
    return total > 0 ? (value / total) * 100 : 0;
}

function getPointsClass(points) {
    if (points === 5) return 'bg-green-100 text-green-800';
    if (points === 3) return 'bg-blue-100 text-blue-800';
    if (points === 1) return 'bg-yellow-100 text-yellow-800';
    return 'bg-gray-100 text-gray-800';
}
</script>
