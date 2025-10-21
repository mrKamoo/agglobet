<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            agglo.bet
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">Pronostiquez sur la Ligue 1 McDonald's</h3>
                    <p class="text-gray-600 mb-4">
                        Compétition de pronostics entre amis - Pas de mise d'argent, juste du fun !
                    </p>
                    @guest
                        <div class="flex gap-4">
                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Se connecter
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                S'inscrire
                            </a>
                        </div>
                    @endguest
                </div>
            </div>

            @if($activeSeason)
                <!-- Upcoming Games -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-xl font-bold mb-4">Prochains matchs</h3>

                        @if($upcomingGames->count() > 0)
                            <div class="space-y-4">
                                @foreach($upcomingGames as $game)
                                    <div class="border rounded-lg p-4 hover:bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 text-center">
                                                <p class="font-semibold">{{ $game->homeTeam->name }}</p><img src="{{ $game->homeTeam->logo }}" alt="{{ $game->homeTeam->name }} Logo" class="mx-auto h-8 w-8">
                                            </div>
                                            <div class="px-6">
                                                <p class="text-gray-500 text-sm">VS</p>
                                                <p class="text-xs text-gray-400">{{ $game->match_date->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <div class="flex-1 text-center">
                                                <p class="font-semibold">{{ $game->awayTeam->name }}</p><img src="{{ $game->awayTeam->logo }}" alt="{{ $game->awayTeam->name }} Logo" class="mx-auto h-8 w-8">
                                            </div>
                                        </div>
                                        @auth
                                            <div class="mt-4 text-center">
                                                <a href="{{ route('games.index', ['matchday' => $game->matchday]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                                    Faire un pronostic →
                                                </a>
                                            </div>
                                        @endauth
                                    </div>
                                @endforeach
                            </div>

                            @auth
                                <div class="mt-6 text-center">
                                    <a href="{{ route('games.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        Voir tous les matchs
                                    </a>
                                </div>
                            @endauth
                        @else
                            <p class="text-gray-500 text-center py-8">Aucun match à venir pour le moment.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                    <p>Aucune saison active pour le moment.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
