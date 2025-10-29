<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Classement Ligue 1
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            

            <!-- Standings Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Équipe
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    MJ
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    G
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    N
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    P
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    BP
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    BC
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Diff
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pts
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($standings as $standing)
                                <tr class="hover:bg-gray-50 transition {{ $loop->iteration <= 3 ? 'bg-green-50' : ($loop->iteration >= count($standings) - 2 ? 'bg-red-50' : '') }}">
                                    <!-- Position -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-bold text-gray-900">{{ $standing['position'] }}</span>
                                            @if($loop->iteration <= 3)
                                                <svg class="w-4 h-4 ml-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            @elseif($loop->iteration >= count($standings) - 2)
                                                <svg class="w-4 h-4 ml-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Team -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if(isset($standing['team']['crest']))
                                                <img src="{{ $standing['team']['crest'] }}" alt="{{ $standing['team']['name'] }}" class="h-8 w-8 object-contain mr-3">
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $standing['team']['name'] ?? $standing['team']['shortName'] ?? 'N/A' }}
                                                </div>
                                                @if(isset($standing['team']['shortName']) && $standing['team']['shortName'] !== $standing['team']['name'])
                                                    <div class="text-xs text-gray-500">
                                                        {{ $standing['team']['shortName'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Stats -->
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $standing['playedGames'] }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-green-600 font-medium">
                                        {{ $standing['won'] }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                        {{ $standing['draw'] }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-red-600 font-medium">
                                        {{ $standing['lost'] }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $standing['goalsFor'] }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $standing['goalsAgainst'] }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-medium {{ $standing['goalDifference'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $standing['goalDifference'] > 0 ? '+' : '' }}{{ $standing['goalDifference'] }}
                                    </td>

                                    <!-- Points -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-bold text-gray-900">{{ $standing['points'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Legend -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex flex-wrap gap-4 text-xs text-gray-600">
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">MJ:</span> Matchs joués
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">G:</span> Gagnés
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">N:</span> Nuls
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">P:</span> Perdus
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">BP:</span> Buts pour
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">BC:</span> Buts contre
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">Diff:</span> Différence de buts
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold mr-1">Pts:</span> Points
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-600">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-50 border border-green-200 mr-2"></div>
                            <span>Ligue des Champions</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-red-50 border border-red-200 mr-2"></div>
                            <span>Zone de relégation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Données fournies par <a href="https://www.football-data.org" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Football-Data.org</a>
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
