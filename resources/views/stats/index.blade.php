<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Statistiques Globales') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Season Selector Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Aperçu général des pronostics</h3>
                        <p class="text-sm text-gray-500">Consultez les tendances, répartitions et favoris de la communauté.</p>
                    </div>
                    <div>
                        <select onchange="window.location.href='{{ route('stats.index') }}?season_id=' + this.value" class="rounded-lg text-sm border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 font-semibold text-gray-700 w-full sm:w-auto">
                            @foreach($seasons as $s)
                                <option value="{{ $s->id }}" {{ $selectedSeasonId == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Predictions -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-400 font-medium uppercase tracking-wider">Pronostics joués</span>
                            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalPredictions) }}</h3>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg text-blue-600 group-hover:bg-blue-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
                </div>

                <!-- Success Rate -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-400 font-medium uppercase tracking-wider">Taux de réussite</span>
                            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $successRate }}%</h3>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg text-green-600 group-hover:bg-green-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 to-green-600"></div>
                </div>

                <!-- Total Points -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-400 font-medium uppercase tracking-wider">Points distribués</span>
                            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalPoints) }}</h3>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg text-purple-600 group-hover:bg-purple-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V5"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-400 to-purple-600"></div>
                </div>

                <!-- Average Points -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-400 font-medium uppercase tracking-wider">Moyenne par prono</span>
                            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $avgPoints }} pts</h3>
                        </div>
                        <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600 group-hover:bg-yellow-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-yellow-400 to-yellow-600"></div>
                </div>
            </div>

            <!-- Charts Section -->
            @if($totalPredictions > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Points Distribution Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h4 class="text-base font-bold text-gray-900 mb-4">Répartition des points gagnés</h4>
                        <div class="relative h-64 flex justify-center">
                            <canvas id="pointsChart"></canvas>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span>
                                <span>Score exact ({{ $pointsRule?->exact_score ?? 5 }} pts) : <strong>{{ $exactScoresCount }}</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-green-500 rounded-full inline-block"></span>
                                <span>Bonne différence ({{ $pointsRule?->correct_difference ?? 3 }} pts) : <strong>{{ $correctDifferencesCount }}</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-yellow-500 rounded-full inline-block"></span>
                                <span>Bon vainqueur ({{ $pointsRule?->correct_winner ?? 1 }} pt) : <strong>{{ $correctWinnersCount }}</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-red-400 rounded-full inline-block"></span>
                                <span>Incorrect (0 pt) : <strong>{{ $incorrectCount }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Predictions vs Reality Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h4 class="text-base font-bold text-gray-900 mb-4">Pronostics vs Réalité (Issues des matchs)</h4>
                        <div class="relative h-64 flex justify-center">
                            <canvas id="outcomesChart"></canvas>
                        </div>
                        <div class="mt-4 flex justify-center gap-6 text-xs text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-blue-600 rounded-sm inline-block"></span>
                                <span>Pronostiqué</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-gray-400 rounded-sm inline-block"></span>
                                <span>Réel</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tendances des prochains matchs -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h4 class="text-base font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="text-blue-500 text-lg">🔮</span> Tendances des prochains matchs (Pronostics de la communauté)
                </h4>
                
                @if($nextGamesStats->count() > 0)
                    <div class="space-y-6">
                        @foreach($nextGamesStats as $stat)
                            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                <!-- Match Info Header -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 text-xs text-gray-500 gap-1">
                                    <div>
                                        <span class="font-semibold text-gray-700">Journée {{ $stat->game->matchday }}</span>
                                        • {{ $stat->game->match_date->format('d/m/Y à H:i') }}
                                    </div>
                                    <div class="font-medium">
                                        {{ $stat->total_predictions }} {{ $stat->total_predictions > 1 ? 'pronostics enregistrés' : 'pronostic enregistré' }}
                                    </div>
                                </div>

                                <!-- Matchup & Progress Bar -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                    <!-- Teams names/logos -->
                                    <div class="md:col-span-5 flex items-center justify-between font-semibold text-sm text-gray-800">
                                        <!-- Home Team -->
                                        <div class="flex items-center gap-2 flex-1 justify-end pr-2 text-right">
                                            <span>{{ $stat->game->homeTeam->short_name ?? $stat->game->homeTeam->name }}</span>
                                            @if($stat->game->homeTeam->logo)
                                                <img src="{{ $stat->game->homeTeam->logo }}" alt="{{ $stat->game->homeTeam->name }}" class="w-6 h-6 object-contain">
                                            @endif
                                        </div>
                                        <span class="text-gray-400 font-bold text-xs px-2 shrink-0">VS</span>
                                        <!-- Away Team -->
                                        <div class="flex items-center gap-2 flex-1 pl-2 text-left">
                                            @if($stat->game->awayTeam->logo)
                                                <img src="{{ $stat->game->awayTeam->logo }}" alt="{{ $stat->game->awayTeam->name }}" class="w-6 h-6 object-contain">
                                            @endif
                                            <span>{{ $stat->game->awayTeam->short_name ?? $stat->game->awayTeam->name }}</span>
                                        </div>
                                    </div>

                                    <!-- Segmented Progress Bar -->
                                    <div class="md:col-span-7 flex flex-col space-y-1.5">
                                        @if($stat->total_predictions > 0)
                                            <!-- Progress bar itself -->
                                            <div class="w-full bg-gray-100 rounded-full h-3 flex overflow-hidden">
                                                <div 
                                                    class="bg-blue-500 h-3 transition-all duration-500" 
                                                    style="width: {{ $stat->home_percent }}%"
                                                    title="Victoire Domicile: {{ $stat->home_percent }}%"
                                                ></div>
                                                <div 
                                                    class="bg-gray-300 h-3 transition-all duration-500" 
                                                    style="width: {{ $stat->draw_percent }}%"
                                                    title="Match Nul: {{ $stat->draw_percent }}%"
                                                ></div>
                                                <div 
                                                    class="bg-indigo-500 h-3 transition-all duration-500" 
                                                    style="width: {{ $stat->away_percent }}%"
                                                    title="Victoire Extérieur: {{ $stat->away_percent }}%"
                                                ></div>
                                            </div>
                                            <!-- Percentages labels -->
                                            <div class="flex justify-between text-[11px] font-semibold">
                                                <span class="text-blue-600">Victoire Domicile : {{ $stat->home_percent }}%</span>
                                                <span class="text-gray-500">Nul : {{ $stat->draw_percent }}%</span>
                                                <span class="text-indigo-600">Victoire Extérieur : {{ $stat->away_percent }}%</span>
                                            </div>
                                        @else
                                            <div class="w-full bg-gray-100 rounded-full h-3 flex items-center justify-center text-[10px] text-gray-400 font-medium">
                                                Aucun pronostic joué
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-6">Aucun match à venir disponible.</p>
                @endif
            </div>

            <!-- Teams Preferences Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Most Predicted Wins -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex flex-col">
                    <h4 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-green-500 text-lg">👍</span> Équipes les plus pronostiquées victorieuses
                    </h4>
                    <div class="flex-1">
                        @if($mostPredictedWins->count() > 0)
                            <div class="divide-y divide-gray-100">
                                @foreach($mostPredictedWins as $index => $team)
                                    <div class="flex items-center justify-between py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-400 w-5">#{{ $index + 1 }}</span>
                                            @if($team->logo)
                                                <img src="{{ $team->logo }}" alt="{{ $team->name }}" class="w-6 h-6 object-contain">
                                            @endif
                                            <span class="text-sm font-medium text-gray-800">{{ $team->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900">{{ $team->win_prediction_count }}</span>
                                            <span class="text-xs text-gray-400">pronos</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-6">Aucune donnée disponible.</p>
                        @endif
                    </div>
                </div>

                <!-- Most Predicted Losses -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex flex-col">
                    <h4 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-red-500 text-lg">👎</span> Équipes les plus pronostiquées perdantes
                    </h4>
                    <div class="flex-1">
                        @if($mostPredictedLosses->count() > 0)
                            <div class="divide-y divide-gray-100">
                                @foreach($mostPredictedLosses as $index => $team)
                                    <div class="flex items-center justify-between py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-400 w-5">#{{ $index + 1 }}</span>
                                            @if($team->logo)
                                                <img src="{{ $team->logo }}" alt="{{ $team->name }}" class="w-6 h-6 object-contain">
                                            @endif
                                            <span class="text-sm font-medium text-gray-800">{{ $team->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900">{{ $team->loss_prediction_count }}</span>
                                            <span class="text-xs text-gray-400">pronos</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-6">Aucune donnée disponible.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Champion Predictions distribution -->
            @if($selectedSeason && $selectedSeason->type === 'tournament' || $championStats->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h4 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-yellow-500 text-lg">🏆</span> Favoris pour le titre (Vainqueur final)
                    </h4>
                    @if($championStats->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @php
                                $maxCount = $championStats->first()->count;
                            @endphp
                            <div class="space-y-4">
                                @foreach($championStats->take(5) as $stat)
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-2">
                                                @if($stat->logo)
                                                    <img src="{{ $stat->logo }}" alt="{{ $stat->name }}" class="w-5 h-5 object-contain">
                                                @endif
                                                <span class="text-sm font-medium text-gray-800">{{ $stat->name }}</span>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ $stat->count }} {{ $stat->count > 1 ? 'votes' : 'vote' }}</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $maxCount > 0 ? ($stat->count / $maxCount) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($championStats->count() > 5)
                                <div class="space-y-4">
                                    @foreach($championStats->slice(5)->take(5) as $stat)
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="flex items-center gap-2">
                                                    @if($stat->logo)
                                                        <img src="{{ $stat->logo }}" alt="{{ $stat->name }}" class="w-5 h-5 object-contain">
                                                    @endif
                                                    <span class="text-sm font-medium text-gray-800">{{ $stat->name }}</span>
                                                </div>
                                                <span class="text-sm font-bold text-gray-900">{{ $stat->count }} {{ $stat->count > 1 ? 'votes' : 'vote' }}</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-2">
                                                <div class="bg-blue-400 h-2 rounded-full" style="width: {{ $maxCount > 0 ? ($stat->count / $maxCount) * 100 : 0 }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-6">Aucun pronostic de vainqueur final n'a été enregistré pour cette saison.</p>
                    @endif
                </div>
            @endif

        </div>
    </div>

    @if($totalPredictions > 0)
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Points Distribution Chart
                    const pointsCtx = document.getElementById('pointsChart').getContext('2d');
                    new Chart(pointsCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Score exact', 'Bonne différence', 'Bon vainqueur', 'Incorrect'],
                            datasets: [{
                                data: [
                                    {{ $exactScoresCount }},
                                    {{ $correctDifferencesCount }},
                                    {{ $correctWinnersCount }},
                                    {{ $incorrectCount }}
                                ],
                                backgroundColor: [
                                    '#3b82f6', // blue-500
                                    '#22c55e', // green-500
                                    '#eab308', // yellow-500
                                    '#f87171'  // red-400
                                ],
                                borderWidth: 2,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });

                    // Outcomes Chart
                    const outcomesCtx = document.getElementById('outcomesChart').getContext('2d');
                    new Chart(outcomesCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Victoire Domicile', 'Match Nul', 'Victoire Extérieur'],
                            datasets: [
                                {
                                    label: 'Pronostiqué',
                                    data: [
                                        {{ $homeWinsPredicted }},
                                        {{ $drawsPredicted }},
                                        {{ $awayWinsPredicted }}
                                    ],
                                    backgroundColor: '#2563eb', // blue-600
                                    borderRadius: 4
                                },
                                {
                                    label: 'Réel',
                                    data: [
                                        {{ $homeWinsActual }},
                                        {{ $drawsActual }},
                                        {{ $awayWinsActual }}
                                    ],
                                    backgroundColor: '#9ca3af', // gray-400
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>
