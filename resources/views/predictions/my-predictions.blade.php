<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mes Pronostics
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($predictions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Match
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Journée
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mon pronostic
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Score final
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Points
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($predictions as $prediction)
                                        <tr class="{{ $prediction->game->is_finished ? 'bg-gray-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-4">
                                                    @if($prediction->game->homeTeam->logo)
                                                        <img src="{{ $prediction->game->homeTeam->logo }}"
                                                             alt="{{ $prediction->game->homeTeam->name }}"
                                                             title="{{ $prediction->game->homeTeam->name }}"
                                                             class="h-10 w-10 object-contain">
                                                    @else
                                                        <div class="h-10 w-10 flex items-center justify-center bg-gray-200 rounded-full text-xs font-semibold text-gray-600">
                                                            {{ substr($prediction->game->homeTeam->short_name ?? $prediction->game->homeTeam->name, 0, 3) }}
                                                        </div>
                                                    @endif

                                                    <span class="text-xs text-gray-500 font-medium">vs</span>

                                                    @if($prediction->game->awayTeam->logo)
                                                        <img src="{{ $prediction->game->awayTeam->logo }}"
                                                             alt="{{ $prediction->game->awayTeam->name }}"
                                                             title="{{ $prediction->game->awayTeam->name }}"
                                                             class="h-10 w-10 object-contain">
                                                    @else
                                                        <div class="h-10 w-10 flex items-center justify-center bg-gray-200 rounded-full text-xs font-semibold text-gray-600">
                                                            {{ substr($prediction->game->awayTeam->short_name ?? $prediction->game->awayTeam->name, 0, 3) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                {{ $prediction->game->matchday }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                {{ $prediction->game->match_date->format('d/m/Y') }}
                                                <div class="text-xs text-gray-400">{{ $prediction->game->match_date->format('H:i') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-lg font-bold text-blue-600">
                                                    {{ $prediction->home_score }} - {{ $prediction->away_score }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($prediction->game->is_finished)
                                                    <span class="text-lg font-bold text-gray-900">
                                                        {{ $prediction->game->home_score }} - {{ $prediction->game->away_score }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($prediction->game->is_finished)
                                                    @if($prediction->points_earned !== null)
                                                        <span class="text-2xl font-bold text-green-600">
                                                            {{ $prediction->points_earned }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400">En attente</span>
                                                    @endif
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($prediction->game->is_finished)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Terminé
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        En attente
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $predictions->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">Vous n'avez pas encore fait de pronostics.</p>
                            <a href="{{ route('games.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Faire mes premiers pronostics
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
