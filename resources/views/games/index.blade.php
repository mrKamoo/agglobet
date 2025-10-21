<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Matchs et Pronostics
            </h2>
            <span class="text-sm text-gray-600">
                @if($activeSeason)
                    {{ $activeSeason->name }}
                @endif
            </span>
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
