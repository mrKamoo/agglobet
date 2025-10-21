<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Classement
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-6">Classement général</h3>

                    @if($leaderboard->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joueur</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pronostics</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($leaderboard as $index => $user)
                                        <tr class="{{ auth()->id() == $user->id ? 'bg-blue-50' : '' }}">
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
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $user->name }}
                                                    @if(auth()->id() == $user->id)
                                                        <span class="ml-2 text-xs text-blue-600">(Vous)</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-sm text-gray-900">{{ $user->predictions_count }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-lg font-bold text-blue-600">{{ $user->total_points }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucun pronostic n'a été fait pour le moment.</p>
                    @endif

                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold mb-2">Système de points</h4>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• <strong>5 points</strong> : Score exact</li>
                            <li>• <strong>3 points</strong> : Bonne différence de buts</li>
                            <li>• <strong>1 point</strong> : Bon vainqueur ou match nul</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
