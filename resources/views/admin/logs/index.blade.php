<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Logs & Historique
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- API Logs Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Synchronisations API Football-Data.org
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détails</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($apiLogs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($log->type == 'matches')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Matchs</span>
                                            @elseif($log->type == 'teams')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Équipes</span>
                                            @elseif($log->type == 'recalc')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Recalcul</span>
                                            @else
                                                {{ $log->type }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($log->status == 'success')
                                                <span class="text-green-600 font-semibold">Succès</span>
                                            @elseif($log->status == 'warning')
                                                <span class="text-yellow-600 font-semibold">Avertissement</span>
                                            @else
                                                <span class="text-red-600 font-semibold">Erreur</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="{{ $log->message }}">
                                            {{ $log->message }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @if(isset($log->details['created']))
                                                <span class="text-xs">
                                                    Créés: {{ $log->details['created'] ?? 0 }} | 
                                                    MAJ: {{ $log->details['updated'] ?? 0 }}
                                                    @if(isset($log->details['skipped']) && $log->details['skipped'] > 0)
                                                     | Ignorés: <span class="text-red-500">{{ $log->details['skipped'] }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Aucun log de synchronisation trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $apiLogs->appends(['pred_page' => $predictions->currentPage()])->links() }}
                    </div>
                </div>
            </div>

            <!-- Predictions Logs Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Derniers Pronostics Joueurs
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Heure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Match</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Prono</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Résultat Match</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($predictions as $prediction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $prediction->updated_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                            {{ $prediction->user->name ?? 'Utilisateur supprimé' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <div class="flex items-center">
                                                @if($prediction->game->homeTeam->logo)
                                                    <img src="{{ $prediction->game->homeTeam->logo }}" class="w-4 h-4 mr-1">
                                                @endif
                                                <span class="font-semibold mr-1">{{ $prediction->game->homeTeam->short_name }}</span>
                                                vs
                                                <span class="font-semibold ml-1 mr-1">{{ $prediction->game->awayTeam->short_name }}</span>
                                                @if($prediction->game->awayTeam->logo)
                                                    <img src="{{ $prediction->game->awayTeam->logo }}" class="w-4 h-4 ml-1">
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">MJ {{ $prediction->game->matchday }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold bg-gray-50">
                                            {{ $prediction->home_score }} - {{ $prediction->away_score }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            @if($prediction->game->is_finished)
                                                {{ $prediction->game->home_score }} - {{ $prediction->game->away_score }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            @if($prediction->game->is_finished)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $prediction->points_earned > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $prediction->points_earned }} pts
                                                </span>
                                            @else
                                                <span class="text-gray-400">En attente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Aucun pronostic trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $predictions->appends(['api_page' => $apiLogs->currentPage()])->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>
