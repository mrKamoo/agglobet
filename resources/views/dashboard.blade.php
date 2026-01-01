<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tableau de bord') }}
            </h2>
            <span class="text-sm text-gray-500">{{ now()->format('d F Y') }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Top Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Rank Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Classement</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $rank }}<sup class="text-sm">ème</sup></p>
                        </div>
                    </div>
                </div>

                <!-- Points Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Points</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalPoints }} <span class="text-sm text-gray-400">pts</span></p>
                        </div>
                    </div>
                </div>

                <!-- Success Rate Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Taux de réussite</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $successRate }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content: Next Match -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Prochain Match</h3>
                            
                            @if($nextGame)
                                <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-xl p-6 text-white relative overflow-hidden">
                                    <!-- Background Pattern -->
                                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full blur-xl"></div>
                                    
                                    <div class="relative z-10">
                                        <div class="text-center text-gray-400 text-sm mb-6">
                                            {{ $nextGame->match_date->format('d/m/Y à H:i') }} • Journée {{ $nextGame->matchday }}
                                        </div>

                                        <div class="flex justify-between items-center mb-8">
                                            <!-- Home Team -->
                                            <div class="flex-1 text-center">
                                                @if($nextGame->homeTeam->logo)
                                                    <img src="{{ $nextGame->homeTeam->logo }}" alt="{{ $nextGame->homeTeam->name }}" class="w-16 h-16 mx-auto mb-2 object-contain bg-white rounded-full p-1">
                                                @else
                                                    <div class="w-16 h-16 mx-auto mb-2 bg-gray-700 rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ substr($nextGame->homeTeam->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div class="font-bold text-lg">{{ $nextGame->homeTeam->short_name ?? $nextGame->homeTeam->name }}</div>
                                            </div>

                                            <!-- VS -->
                                            <div class="px-4">
                                                <span class="text-2xl font-bold text-gray-500">VS</span>
                                            </div>

                                            <!-- Away Team -->
                                            <div class="flex-1 text-center">
                                                @if($nextGame->awayTeam->logo)
                                                    <img src="{{ $nextGame->awayTeam->logo }}" alt="{{ $nextGame->awayTeam->name }}" class="w-16 h-16 mx-auto mb-2 object-contain bg-white rounded-full p-1">
                                                @else
                                                    <div class="w-16 h-16 mx-auto mb-2 bg-gray-700 rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ substr($nextGame->awayTeam->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div class="font-bold text-lg">{{ $nextGame->awayTeam->short_name ?? $nextGame->awayTeam->name }}</div>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            @if($hasPredictedNextGame)
                                                <div class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Pronostic enregistré
                                                </div>
                                                <a href="{{ route('games.index') }}" class="ml-2 text-sm text-gray-400 hover:text-white underline">Modifier</a>
                                            @else
                                                <a href="{{ route('games.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                    Faire mon pronostic
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    Aucun match à venir pour le moment.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Recent Activity -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Derniers résultats</h3>
                            
                            @if($recentPredictions->count() > 0)
                                <div class="space-y-4">
                                    @foreach($recentPredictions as $prediction)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex-1">
                                                <div class="text-xs text-gray-500 mb-1">{{ $prediction->game->match_date->format('d/m') }}</div>
                                                <div class="text-sm font-medium">
                                                    {{ $prediction->game->homeTeam->short_name ?? substr($prediction->game->homeTeam->name, 0, 3) }} 
                                                    <span class="text-gray-400 mx-1">vs</span> 
                                                    {{ $prediction->game->awayTeam->short_name ?? substr($prediction->game->awayTeam->name, 0, 3) }}
                                                </div>
                                                <div class="text-xs mt-1">
                                                    Prono: {{ $prediction->home_score }}-{{ $prediction->away_score }} 
                                                    <span class="text-gray-400">|</span> 
                                                    Réel: {{ $prediction->game->home_score }}-{{ $prediction->game->away_score }}
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col items-end">
                                                @if($prediction->points_earned > 0)
                                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        +{{ $prediction->points_earned }} pts
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        0 pt
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="{{ route('games.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Voir tout l'historique</a>
                                </div>
                            @else
                                <div class="text-center py-4 text-gray-500 text-sm">
                                    Pas encore de résultats disponibles.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('games.index') }}" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group">
                    <div class="w-10 h-10 mx-auto bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-2 group-hover:bg-indigo-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Matchs</span>
                </a>
                
                <a href="{{ route('leaderboard') }}" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group">
                    <div class="w-10 h-10 mx-auto bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mb-2 group-hover:bg-yellow-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Classement</span>
                </a>
                
                <a href="{{ route('profile.edit') }}" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group">
                    <div class="w-10 h-10 mx-auto bg-gray-50 text-gray-600 rounded-full flex items-center justify-center mb-2 group-hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Profil</span>
                </a>

                <a href="#" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group opacity-50 cursor-not-allowed">
                    <div class="w-10 h-10 mx-auto bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Ligues (Bientôt)</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
