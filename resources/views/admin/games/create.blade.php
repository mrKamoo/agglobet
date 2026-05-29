<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Créer un Match
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.games.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="season_id" class="block text-sm font-medium text-gray-700">Saison</label>
                            <select name="season_id" id="season_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Sélectionner une saison</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ old('season_id') == $season->id ? 'selected' : '' }}>
                                        {{ $season->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('season_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="matchday" class="block text-sm font-medium text-gray-700">Journée</label>
                            <input type="number" name="matchday" id="matchday" min="1" max="34" value="{{ old('matchday') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('matchday')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="home_team_id" class="block text-sm font-medium text-gray-700">Équipe domicile</label>
                            <select name="home_team_id" id="home_team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Sélectionner une équipe</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('home_team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('home_team_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="away_team_id" class="block text-sm font-medium text-gray-700">Équipe extérieur</label>
                            <select name="away_team_id" id="away_team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Sélectionner une équipe</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('away_team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('away_team_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="match_date" class="block text-sm font-medium text-gray-700">Date et heure du match</label>
                            <input type="datetime-local" name="match_date" id="match_date" value="{{ old('match_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('match_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4 border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Champs Tournoi (optionnels)</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="round" class="block text-sm font-medium text-gray-700">Phase / Tour</label>
                                    <select name="round" id="round" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Championnat --</option>
                                        <option value="Phase de groupes" {{ old('round') === 'Phase de groupes' ? 'selected' : '' }}>Phase de groupes</option>
                                        <option value="16es de finale" {{ old('round') === '16es de finale' ? 'selected' : '' }}>16es de finale</option>
                                        <option value="8es de finale" {{ old('round') === '8es de finale' ? 'selected' : '' }}>8es de finale</option>
                                        <option value="Quarts de finale" {{ old('round') === 'Quarts de finale' ? 'selected' : '' }}>Quarts de finale</option>
                                        <option value="Demi-finale" {{ old('round') === 'Demi-finale' ? 'selected' : '' }}>Demi-finale</option>
                                        <option value="Match pour la 3e place" {{ old('round') === 'Match pour la 3e place' ? 'selected' : '' }}>Match pour la 3e place</option>
                                        <option value="Finale" {{ old('round') === 'Finale' ? 'selected' : '' }}>Finale</option>
                                    </select>
                                    @error('round')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="group" class="block text-sm font-medium text-gray-700">Groupe (A-L)</label>
                                    <input type="text" name="group" id="group" value="{{ old('group') }}" maxlength="2" placeholder="Ex: A" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase">
                                    @error('group')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="stadium" class="block text-sm font-medium text-gray-700">Stade / Lieu</label>
                                    <input type="text" name="stadium" id="stadium" value="{{ old('stadium') }}" placeholder="Ex: MetLife Stadium, New York" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('stadium')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.games.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Annuler
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
