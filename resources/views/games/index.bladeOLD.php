<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Matchs - Journée {{ $matchday }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Matchday Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <label for="matchday" class="block text-sm font-medium text-gray-700 mb-2">Sélectionner une journée</label>
                    <select id="matchday" onchange="window.location.href='{{ route('games.index') }}?matchday=' + this.value" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        @foreach($matchdays as $day)
                            <option value="{{ $day }}" {{ $day == $matchday ? 'selected' : '' }}>
                                Journée {{ $day }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Games List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($games->count() > 0)
                        <div class="space-y-6">
                            @foreach($games as $game)
                                <div class="border rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex-1 text-center">
                                            <p class="text-lg font-bold">{{ $game->homeTeam->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $game->homeTeam->short_name }}</p>
                                        </div>

                                        <div class="px-8">
                                            @if($game->is_finished)
                                                <div class="text-center">
                                                    <div class="text-2xl font-bold">
                                                        {{ $game->home_score }} - {{ $game->away_score }}
                                                    </div>
                                                    <p class="text-xs text-gray-400">Terminé</p>
                                                </div>
                                            @else
                                                <div class="text-center">
                                                    <p class="text-gray-500 font-semibold">VS</p>
                                                    <p class="text-sm text-gray-400">{{ $game->match_date->format('d/m/Y') }}</p>
                                                    <p class="text-sm text-gray-400">{{ $game->match_date->format('H:i') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 text-center">
                                            <p class="text-lg font-bold">{{ $game->awayTeam->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $game->awayTeam->short_name }}</p>
                                        </div>
                                    </div>

                                    @if(!$game->is_finished && !$game->match_date->isPast())
                                        <!-- Prediction Form -->
                                        @php
                                            $userPrediction = $game->predictions->where('user_id', auth()->id())->first();
                                        @endphp
                                        <form action="{{ route('predictions.store', $game) }}" method="POST" class="mt-4">
                                            @csrf
                                            <div class="flex items-center justify-center gap-4">
                                                <div>
                                                    <label class="block text-xs text-gray-600 mb-1">Score domicile</label>
                                                    <input type="number" name="home_score" min="0" max="20" value="{{ $userPrediction?->home_score }}" class="w-20 text-center border-gray-300 rounded-md" required>
                                                </div>
                                                <span class="mt-5">-</span>
                                                <div>
                                                    <label class="block text-xs text-gray-600 mb-1">Score extérieur</label>
                                                    <input type="number" name="away_score" min="0" max="20" value="{{ $userPrediction?->away_score }}" class="w-20 text-center border-gray-300 rounded-md" required>
                                                </div>
                                                <button type="submit" class="mt-5 px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                                    {{ $userPrediction ? 'Modifier' : 'Pronostiquer' }}
                                                </button>
                                            </div>
                                        </form>
                                        @if($userPrediction)
                                            <p class="text-center text-sm text-green-600 mt-2">✓ Pronostic enregistré</p>
                                        @endif
                                    @elseif($game->match_date->isPast() && !$game->is_finished)
                                        <p class="text-center text-sm text-orange-600 mt-2">Match en cours - Pronostics fermés</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucun match pour cette journée.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</x-app-layout>
