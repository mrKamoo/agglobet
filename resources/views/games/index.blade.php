<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Matchs et Pronostics
            </h2>
            <div class="flex items-center gap-2">
                <select onchange="window.location.href='{{ route('games.index') }}?season_id=' + this.value" class="rounded-lg text-sm border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 font-semibold text-gray-700">
                    @foreach($seasons as $s)
                        <option value="{{ $s->id }}" {{ $selectedSeasonId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Vue.js Games List Component -->
            <div id="app">
                <games-list></games-list>
            </div>
        </div>
    </div>
</x-app-layout>
