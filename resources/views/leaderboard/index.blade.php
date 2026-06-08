<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Classement
        </h2>
    </x-slot>

    <div id="leaderboard-app" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Period Leaders -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($weekLeader)
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium opacity-90">Meilleur de la semaine</div>
                            <div class="text-2xl font-bold mt-1">{{ $weekLeader->name }}</div>
                            <div class="text-3xl font-bold mt-2">{{ $weekLeader->total_points }} pts</div>
                        </div>
                        <div class="text-6xl">🏆</div>
                    </div>
                </div>
                @endif

                @if($monthLeader)
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium opacity-90">Meilleur du mois</div>
                            <div class="text-2xl font-bold mt-1">{{ $monthLeader->name }}</div>
                            <div class="text-3xl font-bold mt-2">{{ $monthLeader->total_points }} pts</div>
                        </div>
                        <div class="text-6xl">👑</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Main Leaderboard -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Header with Filters -->
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 gap-4">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <h3 class="text-xl font-bold">Classement général</h3>
                            
                            <!-- Season Selector -->
                            <div class="flex items-center gap-2">
                                <select onchange="window.location.href='{{ route('leaderboard') }}?season_id=' + this.value" class="rounded-lg text-sm border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 font-semibold text-gray-700">
                                    @foreach($seasons as $s)
                                        <option value="{{ $s->id }}" {{ $selectedSeasonId == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Period Filter -->
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('leaderboard', ['period' => 'all', 'season_id' => $selectedSeasonId]) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Tout
                            </a>
                            <a href="{{ route('leaderboard', ['period' => 'month', 'season_id' => $selectedSeasonId]) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Ce mois
                            </a>
                            <a href="{{ route('leaderboard', ['period' => 'week', 'season_id' => $selectedSeasonId]) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Cette semaine
                            </a>

                            <!-- Matchday Dropdown -->
                            <select onchange="window.location.href=this.value" class="px-4 py-2 rounded-lg text-sm font-medium border-gray-300 {{ $period === 'matchday' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                <option value="{{ route('leaderboard', ['season_id' => $selectedSeasonId]) }}">Par journée</option>
                                @foreach($matchdays as $md)
                                    <option value="{{ route('leaderboard', ['period' => 'matchday', 'matchday' => $md, 'season_id' => $selectedSeasonId]) }}"
                                            {{ $period === 'matchday' && $matchday == $md ? 'selected' : '' }}>
                                        Journée {{ $md }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($leaderboard->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joueur</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pronostics</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Taux réussite</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Moy/match</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Série</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($leaderboard as $index => $user)
                                        <tr class="{{ auth()->id() == $user->id ? 'bg-blue-50' : '' }} hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($index === 0)
                                                        <span class="text-2xl">🥇</span>
                                                    @elseif($index === 1)
                                                        <span class="text-2xl">🥈</span>
                                                    @elseif($index === 2)
                                                        <span class="text-2xl">🥉</span>
                                                    @else
                                                        <span class="text-lg font-semibold text-gray-600">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                                            <span>{{ $user->name }}</span>
                                                            @if($user->champion_team)
                                                                @if($user->champion_team->logo)
                                                                    <img src="{{ $user->champion_team->logo }}" alt="{{ $user->champion_team->name }}" class="h-5 w-5 object-contain" title="Vainqueur final pronostiqué : {{ $user->champion_team->name }}">
                                                                @else
                                                                    <span class="text-xs text-gray-500" title="Vainqueur final pronostiqué : {{ $user->champion_team->name }}">({{ $user->champion_team->short_name }})</span>
                                                                @endif
                                                            @endif
                                                            @if(auth()->id() == $user->id)
                                                                <span class="text-xs text-blue-600">(Vous)</span>
                                                            @endif
                                                        </div>
                                                        <div class="flex gap-1 mt-1">
                                                            @if($user->exact_scores >= 5)
                                                                <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full" title="5+ scores exacts">🎯 Sniper</span>
                                                            @endif
                                                            @if($user->current_streak >= 5)
                                                                <span class="text-xs px-2 py-0.5 bg-red-100 text-red-800 rounded-full" title="Série de {{ $user->current_streak }}">🔥 En feu</span>
                                                            @endif
                                                            @if($user->success_rate >= 80)
                                                                <span class="text-xs px-2 py-0.5 bg-green-100 text-green-800 rounded-full" title="Taux de réussite {{ $user->success_rate }}%">⭐ Expert</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-sm text-gray-900">{{ $user->predictions_count }}</div>
                                                <div class="text-xs text-gray-500">
                                                    <span class="text-green-600" title="Scores exacts">{{ $user->exact_scores }}</span> /
                                                    <span class="text-blue-600" title="Bonnes différences">{{ $user->correct_differences }}</span> /
                                                    <span class="text-yellow-600" title="Bons vainqueurs">{{ $user->correct_winners }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center">
                                                    <div class="text-sm font-medium {{ $user->success_rate >= 70 ? 'text-green-600' : ($user->success_rate >= 50 ? 'text-blue-600' : 'text-gray-600') }}">
                                                        {{ $user->success_rate }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->avg_points }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    @if($user->current_streak > 0)
                                                        <span class="text-orange-500">🔥</span>
                                                        <span class="text-sm font-bold text-gray-900">{{ $user->current_streak }}</span>
                                                    @else
                                                        <span class="text-sm text-gray-400">-</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-lg font-bold text-blue-600">{{ $user->total_points }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <button @click="openUserStats({{ $user->id }})"
                                                        class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition">
                                                    Détails
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucun pronostic n'a été fait pour cette période.</p>
                    @endif

                    <!-- Points System Info -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold mb-2">Système de points</h4>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• <strong>{{ $pointsRule?->exact_score ?? 5 }} points</strong> : Score exact</li>
                            <li>• <strong>{{ $pointsRule?->correct_difference ?? 3 }} points</strong> : Bonne différence de buts</li>
                            <li>• <strong>{{ $pointsRule?->correct_winner ?? 1 }} point</strong> : Bon vainqueur ou match nul</li>
                        </ul>
                    </div>

                    <!-- Badges Info -->
                    <div class="mt-4 p-4 bg-purple-50 rounded-lg">
                        <h4 class="font-semibold mb-2">Badges disponibles</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🎯</span>
                                <div>
                                    <div class="font-medium">Sniper</div>
                                    <div class="text-xs text-gray-600">5+ scores exacts</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🔥</span>
                                <div>
                                    <div class="font-medium">En feu</div>
                                    <div class="text-xs text-gray-600">Série de 5+ matchs</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">⭐</span>
                                <div>
                                    <div class="font-medium">Expert</div>
                                    <div class="text-xs text-gray-600">80%+ de réussite</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Stats Modal -->
        <user-stats-modal
            :user-id="selectedUserId"
            :is-open="isModalOpen"
            @close="closeUserStats">
        </user-stats-modal>
    </div>

    @push('scripts')
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for Vue and components to be loaded
            const initLeaderboard = () => {
                if (typeof window.Vue === 'undefined' || typeof window.UserStatsModal === 'undefined') {
                    setTimeout(initLeaderboard, 100);
                    return;
                }

                const { createApp } = window.Vue;

                createApp({
                    data() {
                        return {
                            selectedUserId: null,
                            isModalOpen: false,
                        };
                    },
                    methods: {
                        openUserStats(userId) {
                            this.selectedUserId = userId;
                            this.isModalOpen = true;
                        },
                        closeUserStats() {
                            this.isModalOpen = false;
                            this.selectedUserId = null;
                        }
                    }
                }).component('user-stats-modal', window.UserStatsModal).mount('#leaderboard-app');
            };

            initLeaderboard();
        });
    </script>
    @endpush
</x-app-layout>
