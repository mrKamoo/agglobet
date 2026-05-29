<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                🏆 Coupe du Monde FIFA 2026
            </h2>
            <div class="text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full font-medium border border-gray-200">
                12 Groupes • 48 Équipes • 104 Matchs
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ activeTab: 'groups', isFullscreen: false, showConfirmModal: false, selectedTeamId: '', teams: {{ $wcTeams->mapWithKeys(fn($t) => [$t->id => $t->name])->toJson() }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- CHAMPION PREDICTION WIDGET -->
            <div class="bg-gradient-to-r from-amber-500/10 via-yellow-500/5 to-orange-500/10 rounded-2xl border border-yellow-500/20 shadow-md p-6 mb-8 relative overflow-hidden">
                <!-- Background decorative elements -->
                <div class="absolute right-0 top-0 translate-x-1/4 -translate-y-1/4 opacity-10 pointer-events-none select-none">
                    <svg class="w-72 h-72 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                    </svg>
                </div>

                <!-- Banner Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 relative z-10">
                    <div>
                        <h3 class="text-lg font-black text-amber-900 flex items-center gap-2">
                            👑 Mon Vainqueur de la Coupe du Monde 2026
                        </h3>
                        <p class="text-sm text-amber-800 mt-1 max-w-2xl font-medium">
                            Sélectionnez votre favori ultime pour soulever le trophée ! Si votre équipe gagne la compétition, vous remporterez un <strong class="text-amber-900 font-extrabold bg-amber-200/50 px-1.5 py-0.5 rounded">bonus de 30 points</strong> au classement général.
                        </p>
                    </div>
                    <div class="bg-amber-500/10 border border-amber-500/20 text-amber-800 text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-sm">
                        📆 Limite : 20/06/2026 à 23h59
                    </div>
                </div>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-4 bg-emerald-100 border border-emerald-300 text-emerald-800 text-sm font-semibold px-4 py-3 rounded-lg flex items-center gap-2 animate-bounce">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-300 text-red-800 text-sm font-semibold px-4 py-3 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Widget Body -->
                <div class="bg-white/80 backdrop-blur rounded-xl p-4 border border-amber-500/10 shadow-sm relative z-10">
                    @if($userChampionPrediction !== null)
                        <!-- OPTION A: Champion already predicted -->
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <div class="w-16 h-16 bg-amber-50 rounded-xl flex items-center justify-center border border-amber-200 flex-shrink-0 shadow-sm">
                                    @if($userChampionPrediction->team->logo)
                                        <img src="{{ $userChampionPrediction->team->logo }}" alt="{{ $userChampionPrediction->team->name }}" class="h-10 w-10 object-contain">
                                    @else
                                        <span class="text-xl font-bold text-amber-600">{{ $userChampionPrediction->team->short_name }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Votre favori sélectionné</div>
                                    <div class="text-2xl font-black text-amber-700 tracking-wide mt-0.5">
                                        {{ $userChampionPrediction->team->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-medium mt-0.5">
                                        🔒 Choix définitif enregistré le {{ $userChampionPrediction->created_at->format('d/m/Y à H\hi') }}
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-auto text-right flex flex-col items-stretch md:items-end gap-2">
                                @if($season->winner_team_id !== null)
                                    @if($userChampionPrediction->team_id === $season->winner_team_id)
                                        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 font-bold px-4 py-2 rounded-lg text-sm flex items-center justify-center gap-1.5 shadow-sm">
                                            🎉 Pronostic Correct ! +30 Pts 🏆
                                        </div>
                                    @else
                                        <div class="bg-gray-100 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-lg text-sm flex items-center justify-center gap-1.5">
                                            ❌ Vainqueur : {{ $season->winner ? $season->winner->name : 'N/A' }}
                                        </div>
                                    @endif
                                @else
                                    <div class="bg-blue-50 border border-blue-200 text-blue-800 font-semibold px-4 py-2 rounded-lg text-xs flex items-center justify-center gap-1.5 shadow-sm">
                                        ⏳ En attente de la finale pour valider le bonus...
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif($championDeadlinePassed)
                        <!-- OPTION B: Deadline passed, no prediction made -->
                        <div class="flex items-center gap-3 text-red-800 py-2">
                            <svg class="w-8 h-8 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0118 0z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <span class="font-bold text-sm block">🔒 Pronostic Fermé</span>
                                <span class="text-xs text-red-700 font-medium">La date limite du 20/06/2026 est dépassée et vous n'avez pas sélectionné d'équipe favorite.</span>
                            </div>
                        </div>
                    @else
                        <!-- OPTION C: Form to predict -->
                        <form x-ref="championForm" action="{{ route('predictions.champion.store', $season->id) }}" method="POST" @submit.prevent="showConfirmModal = true" class="flex flex-col md:flex-row items-stretch md:items-center gap-4">
                            @csrf
                            <div class="flex-1 flex flex-col gap-1">
                                <label for="team_id" class="text-xs font-bold text-gray-500 uppercase tracking-wide">Sélectionnez une nation :</label>
                                <select 
                                    name="team_id" 
                                    id="team_id" 
                                    required 
                                    x-model="selectedTeamId"
                                    class="w-full rounded-lg border-2 border-amber-300 focus:border-amber-500 focus:ring-4 focus:ring-amber-100 transition-all font-semibold text-gray-800 shadow-sm"
                                >
                                    <option value="" disabled selected>-- Choisissez votre favori --</option>
                                    @foreach($wcTeams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex flex-col gap-2">
                                <span class="hidden md:block h-5"></span> <!-- Spacer alignment -->
                                <button 
                                    type="submit" 
                                    :disabled="!selectedTeamId"
                                    :class="!selectedTeamId ? 'opacity-60 cursor-not-allowed' : 'hover:scale-[1.02] transform'"
                                    class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-black text-sm px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
                                >
                                    🏆 Valider mon Favori (Définitif)
                                </button>
                            </div>
                        </form>
                        <div class="mt-3 text-[10px] text-amber-700 font-semibold bg-amber-50 border border-amber-200/50 p-2.5 rounded-lg flex items-center gap-2">
                            <span>⚠️ Attention :</span> Ce choix est **définitif et irréversible**. Aucun changement ne sera possible après validation.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="flex justify-center border-b border-gray-200 mb-8 bg-white p-2 rounded-xl shadow-sm gap-2">
                <button 
                    @click="activeTab = 'groups'" 
                    :class="activeTab === 'groups' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                    class="py-2.5 px-6 font-semibold text-sm rounded-lg transition-all duration-200 flex items-center gap-2"
                >
                    ⚽ Phase de Groupes
                </button>
                <button 
                    @click="activeTab = 'bracket'" 
                    :class="activeTab === 'bracket' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                    class="py-2.5 px-6 font-semibold text-sm rounded-lg transition-all duration-200 flex items-center gap-2"
                >
                    🌳 Tableau Final (Knockout)
                </button>
            </div>

            <!-- TAB 1: PHASE DE GROUPES -->
            <div x-show="activeTab === 'groups'" class="space-y-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($groupStandings as $groupName => $teams)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                            <!-- Group Title -->
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 text-white flex justify-between items-center">
                                <h3 class="font-bold text-base tracking-wide">GROUPE {{ $groupName }}</h3>
                                <span class="text-[10px] font-semibold bg-white/20 px-2 py-0.5 rounded-full uppercase tracking-wider">Poule</span>
                            </div>

                            <!-- Group Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                                            <th class="px-3 py-2 text-center w-8">#</th>
                                            <th class="px-3 py-2 text-left">Équipe</th>
                                            <th class="px-2 py-2 text-center">MJ</th>
                                            <th class="px-2 py-2 text-center">Diff</th>
                                            <th class="px-3 py-2 text-center">Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-sm">
                                        @php $pos = 1; @endphp
                                        @foreach($teams as $teamId => $stats)
                                            @if(!$stats['team']) @php $pos++; @endphp @continue @endif
                                            <tr class="hover:bg-gray-50 transition-colors {{ $pos <= 2 ? 'bg-emerald-50/30' : ($pos === 3 ? 'bg-blue-50/20' : '') }}">
                                                <!-- Position -->
                                                <td class="px-3 py-2.5 text-center font-bold {{ $pos <= 2 ? 'text-emerald-700' : ($pos === 3 ? 'text-blue-700' : 'text-gray-400') }}">
                                                    {{ $pos }}
                                                </td>
                                                
                                                <!-- Team Name & Logo -->
                                                <td class="px-3 py-2.5 font-medium text-gray-800">
                                                    <div class="flex items-center gap-2">
                                                        @if($stats['team']->logo)
                                                            <img src="{{ $stats['team']->logo }}" alt="{{ $stats['team']->name }}" class="h-5 w-5 object-contain flex-shrink-0">
                                                        @else
                                                            <div class="h-5 w-5 bg-gray-100 rounded-full flex items-center justify-center text-[10px] font-bold text-gray-400 flex-shrink-0">
                                                                {{ $stats['team']->short_name }}
                                                            </div>
                                                        @endif
                                                        <span class="truncate" title="{{ $stats['team']->name }}">{{ $stats['team']->name }}</span>
                                                    </div>
                                                </td>

                                                <!-- Matchs Joués -->
                                                <td class="px-2 py-2.5 text-center text-gray-500 font-medium">
                                                    {{ $stats['played'] }}
                                                </td>

                                                <!-- Goal Difference -->
                                                <td class="px-2 py-2.5 text-center font-semibold {{ $stats['goal_diff'] > 0 ? 'text-emerald-600' : ($stats['goal_diff'] < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                                    {{ $stats['goal_diff'] > 0 ? '+' : '' }}{{ $stats['goal_diff'] }}
                                                </td>

                                                <!-- Points -->
                                                <td class="px-3 py-2.5 text-center font-bold text-gray-900">
                                                    {{ $stats['points'] }}
                                                </td>
                                            </tr>
                                            @php $pos++; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Info Cards -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3 text-sm text-blue-800">
                    <svg class="w-5 h-5 flex-shrink-0 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-semibold block mb-1">Règle de Qualification Coupe du Monde 2026 :</span>
                        Les <strong class="text-blue-900">2 premiers de chaque groupe</strong> (24 équipes) ainsi que les <strong class="text-blue-900">8 meilleurs 3es</strong> (8 équipes) se qualifient pour les 16es de finale. Les lignes vertes indiquent la qualification directe, les lignes bleues indiquent le classement d'attente pour le repêchage des meilleurs 3es.
                    </div>
                </div>
            </div>

            <!-- TAB 2: TABLEAU FINAL (KNOCKOUT BRACKET) -->
            <div x-show="activeTab === 'bracket'" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                
                <!-- Fullscreen Wrapper -->
                <div :class="isFullscreen ? 'fixed inset-0 z-50 bg-slate-900 w-screen h-screen flex flex-col p-4 md:p-8' : 'relative'">
                    
                    <!-- Fullscreen Toggle Button -->
                    <button @click="isFullscreen = !isFullscreen" 
                            class="absolute top-4 right-4 z-20 bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white p-2.5 rounded-lg backdrop-blur shadow-lg border border-slate-600 transition-all flex items-center gap-2"
                            :class="isFullscreen ? 'fixed top-6 right-6' : 'absolute top-4 right-4'">
                        <span x-show="!isFullscreen" class="text-xs font-bold uppercase tracking-wider hidden sm:block">Agrandir</span>
                        <span x-show="isFullscreen" x-cloak class="text-xs font-bold uppercase tracking-wider hidden sm:block">Fermer</span>
                        
                        <svg x-show="!isFullscreen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <svg x-show="isFullscreen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Header in fullscreen mode -->
                    <div x-show="isFullscreen" x-cloak class="flex-shrink-0 mb-6 text-center">
                        <h2 class="text-2xl font-black text-white uppercase tracking-widest flex items-center justify-center gap-3">
                            <span>🏆</span> Arbre Final - Coupe du Monde 2026 <span>🏆</span>
                        </h2>
                    </div>

                    <!-- Horizontal Scroll Container for the Bracket -->
                    <div class="overflow-x-auto rounded-xl bg-slate-900 border border-slate-800 shadow-inner flex-1"
                         :class="isFullscreen ? 'h-full w-full rounded-none border-none pb-0' : 'pb-8'">
                        <div class="min-w-[2000px] flex p-6 gap-4 sm:gap-6 select-none relative h-full" :class="isFullscreen ? 'items-center justify-center' : ''">
                            <!-- ================= LEFT SIDE ================= -->
                            <!-- Col 1: 16es de finale (Gauche) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">16es</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['16es de finale'])->take(8) as $game)
                                        <div class="h-[96px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -right-3 w-3 h-px bg-slate-700"></div>
                                            @if($loop->iteration % 2 !== 0)
                                                <div class="absolute top-1/2 -right-3 w-px h-[48px] bg-slate-700"></div>
                                            @else
                                                <div class="absolute bottom-1/2 -right-3 w-px h-[48px] bg-slate-700"></div>
                                            @endif
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Col 2: 8es de finale (Gauche) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">8es</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['8es de finale'])->take(4) as $game)
                                        <div class="h-[192px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -left-3 w-3 h-px bg-slate-700"></div>
                                            <div class="absolute top-1/2 -right-3 w-3 h-px bg-slate-700"></div>
                                            @if($loop->iteration % 2 !== 0)
                                                <div class="absolute top-1/2 -right-3 w-px h-[96px] bg-slate-700"></div>
                                            @else
                                                <div class="absolute bottom-1/2 -right-3 w-px h-[96px] bg-slate-700"></div>
                                            @endif
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Col 3: Quarts de finale (Gauche) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">Quarts</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['Quarts de finale'])->take(2) as $game)
                                        <div class="h-[384px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -left-3 w-3 h-px bg-slate-700"></div>
                                            <div class="absolute top-1/2 -right-3 w-3 h-px bg-slate-700"></div>
                                            @if($loop->iteration % 2 !== 0)
                                                <div class="absolute top-1/2 -right-3 w-px h-[192px] bg-slate-700"></div>
                                            @else
                                                <div class="absolute bottom-1/2 -right-3 w-px h-[192px] bg-slate-700"></div>
                                            @endif
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Col 4: Demi-finale (Gauche) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">Demi</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['Demi-finale'])->take(1) as $game)
                                        <div class="h-[768px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -left-3 w-3 h-px bg-slate-700"></div>
                                            <div class="absolute top-1/2 -right-3 w-3 h-px bg-slate-700"></div>
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- ================= CENTER ================= -->
                            <!-- Col 5: Finale & 3e Place -->
                            <div class="w-[260px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-yellow-500 font-bold text-xs uppercase tracking-widest text-center border-b border-yellow-500/30 mb-2">Finale</div>
                                
                                <div class="h-[768px] w-full relative">
                                    <!-- Finale -->
                                    @foreach($knockoutRounds['Finale'] as $game)
                                    <div class="absolute top-1/2 -translate-y-1/2 w-full">
                                        <div class="absolute top-1/2 -left-3 w-3 h-px bg-yellow-600"></div>
                                        <div class="absolute top-1/2 -right-3 w-3 h-px bg-yellow-600"></div>
                                    <div class="bg-gradient-to-b from-slate-800 to-amber-950/20 rounded-lg p-3 border-2 border-yellow-500 shadow-lg shadow-yellow-500/10 hover:border-yellow-400 transition duration-200 relative z-10 w-full">
                                        <div class="text-[10px] text-yellow-500 mb-2 flex justify-between font-bold uppercase tracking-wider">
                                            <span>Finale</span>
                                            <span>{{ $game->match_date->format('d/m') }}</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center text-sm">
                                                <div class="flex items-center gap-2 truncate">
                                                    @if($game->homeTeam && $game->homeTeam->logo)
                                                        <img src="{{ $game->homeTeam->logo }}" class="w-4 h-4 object-contain">
                                                    @endif
                                                    <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-yellow-400 font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-100') }} truncate">
                                                        {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                    </span>
                                                </div>
                                                <span class="font-bold text-sm {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-yellow-400' : 'text-slate-400' }}">
                                                    {{ $game->is_finished ? $game->home_score : '-' }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm">
                                                <div class="flex items-center gap-2 truncate">
                                                    @if($game->awayTeam && $game->awayTeam->logo)
                                                        <img src="{{ $game->awayTeam->logo }}" class="w-4 h-4 object-contain">
                                                    @endif
                                                    <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-yellow-400 font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-100') }} truncate">
                                                        {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                    </span>
                                                </div>
                                                <span class="font-bold text-sm {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-yellow-400' : 'text-slate-400' }}">
                                                    {{ $game->is_finished ? $game->away_score : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-[9px] text-amber-500/70 truncate mt-2 text-center font-medium" title="{{ $game->stadium }}">
                                            📍 {{ explode(',', $game->stadium)[0] }}
                                        </div>
                                    </div>
                                    </div>
                                    @endforeach

                                    <!-- Petite Finale -->
                                    @foreach($knockoutRounds['Match pour la 3e place'] as $game)
                                    <div class="absolute bottom-4 w-full">
                                        <div class="text-slate-400 font-bold text-[10px] uppercase tracking-widest text-center mb-2">🥉 3e Place</div>
                                    <div class="bg-slate-800 rounded-lg p-2 border border-slate-600 hover:border-slate-400 transition duration-200 relative z-10 w-full opacity-80">
                                        <div class="text-[8px] text-slate-400 mb-1 flex justify-between">
                                            <span>Match {{ $game->id }}</span>
                                            <span>{{ $game->match_date->format('d/m') }}</span>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="flex justify-between items-center text-xs">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    @if($game->homeTeam && $game->homeTeam->logo)
                                                        <img src="{{ $game->homeTeam->logo }}" class="w-3 h-3 object-contain">
                                                    @endif
                                                    <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                        {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                    </span>
                                                </div>
                                                <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                    {{ $game->is_finished ? $game->home_score : '-' }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    @if($game->awayTeam && $game->awayTeam->logo)
                                                        <img src="{{ $game->awayTeam->logo }}" class="w-3 h-3 object-contain">
                                                    @endif
                                                    <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                        {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                    </span>
                                                </div>
                                                <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                    {{ $game->is_finished ? $game->away_score : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- ================= RIGHT SIDE ================= -->
                            <!-- Col 6: Demi-finale (Droite) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">Demi</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['Demi-finale'])->skip(1)->take(1) as $game)
                                        <div class="h-[768px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -left-3 w-3 h-px bg-slate-700"></div>
                                            <div class="absolute top-1/2 -right-3 w-3 h-px bg-slate-700"></div>
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Col 7: Quarts de finale (Droite) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">Quarts</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['Quarts de finale'])->skip(2)->take(2) as $game)
                                        <div class="h-[384px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -right-3 w-3 h-px bg-slate-700"></div>
                                            <div class="absolute top-1/2 -left-3 w-3 h-px bg-slate-700"></div>
                                            @if($loop->iteration % 2 !== 0)
                                                <div class="absolute top-1/2 -left-3 w-px h-[192px] bg-slate-700"></div>
                                            @else
                                                <div class="absolute bottom-1/2 -left-3 w-px h-[192px] bg-slate-700"></div>
                                            @endif
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Col 8: 8es de finale (Droite) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">8es</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['8es de finale'])->skip(4)->take(4) as $game)
                                        <div class="h-[192px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -right-3 w-3 h-px bg-slate-700"></div>
                                            <div class="absolute top-1/2 -left-3 w-3 h-px bg-slate-700"></div>
                                            @if($loop->iteration % 2 !== 0)
                                                <div class="absolute top-1/2 -left-3 w-px h-[96px] bg-slate-700"></div>
                                            @else
                                                <div class="absolute bottom-1/2 -left-3 w-px h-[96px] bg-slate-700"></div>
                                            @endif
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Col 9: 16es de finale (Droite) -->
                            <div class="w-[220px] flex-shrink-0 relative">
                                <div class="h-[30px] flex items-center justify-center text-slate-400 font-bold text-xs uppercase tracking-widest text-center border-b border-slate-800 mb-2">16es</div>
                                <div class="flex flex-col">
                                    @foreach(collect($knockoutRounds['16es de finale'])->skip(8)->take(8) as $game)
                                        <div class="h-[96px] w-full flex flex-col justify-center relative">
                                            <!-- Connectors -->
                                            <div class="absolute top-1/2 -left-3 w-3 h-px bg-slate-700"></div>
                                            @if($loop->iteration % 2 !== 0)
                                                <div class="absolute top-1/2 -left-3 w-px h-[48px] bg-slate-700"></div>
                                            @else
                                                <div class="absolute bottom-1/2 -left-3 w-px h-[48px] bg-slate-700"></div>
                                            @endif
                                        <div class="bg-slate-800 rounded-lg p-2 border border-slate-700 hover:border-blue-500/50 transition duration-200 relative z-10 w-full shadow-md">
                                            <div class="text-[9px] text-slate-400 mb-1 flex justify-between">
                                                <span>Match {{ $game->id }}</span>
                                                <span>{{ $game->match_date->format('d/m') }}</span>
                                            </div>
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->homeTeam && $game->homeTeam->logo)
                                                            <img src="{{ $game->homeTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->home_score > $game->away_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->homeTeam ? $game->homeTeam->short_name : $game->home_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->home_score > $game->away_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->home_score : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        @if($game->awayTeam && $game->awayTeam->logo)
                                                            <img src="{{ $game->awayTeam->logo }}" class="w-3.5 h-3.5 object-contain">
                                                        @endif
                                                        <span class="{{ $game->is_finished && $game->away_score > $game->home_score ? 'text-white font-bold' : ($game->is_finished ? 'text-slate-500' : 'text-slate-300') }} truncate">
                                                            {{ $game->awayTeam ? $game->awayTeam->short_name : $game->away_team_placeholder }}
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-xs {{ $game->is_finished && $game->away_score > $game->home_score ? 'text-emerald-400' : 'text-slate-400' }}">
                                                        {{ $game->is_finished ? $game->away_score : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-500 truncate mt-1 text-center" title="{{ $game->stadium }}">
                                                📍 {{ explode(',', $game->stadium)[0] }}
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>                        </div>
                </div>

                <!-- Touch scroll note -->
                <div x-show="!isFullscreen" class="text-center text-xs text-gray-500 flex items-center justify-center gap-1.5 mt-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>Faites glisser horizontalement pour explorer tout le tableau final (des 16es à la Grande Finale).</span>
                </div>

                </div> <!-- End Fullscreen Wrapper -->
            </div>

            <!-- CUSTOM CONFIRM MODAL -->
            <div 
                x-show="showConfirmModal" 
                class="fixed inset-0 z-50 overflow-y-auto"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display: none;"
            >
                <!-- Backdrop overlay -->
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showConfirmModal = false"></div>

                <!-- Modal container -->
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div 
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 w-full"></div>

                        <div class="p-6">
                            <!-- Icon & Title -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-xl shadow-sm border border-amber-100 flex-shrink-0 animate-bounce">
                                    🏆
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 leading-6">
                                    Confirmer votre favori
                                </h3>
                            </div>

                            <!-- Content -->
                            <div class="space-y-4 text-sm text-gray-600">
                                <p>
                                    Vous êtes sur le point de valider votre favori pour le titre de champion du monde :
                                </p>
                                
                                <div class="bg-amber-50 border border-amber-200/60 rounded-xl p-4 flex items-center justify-center gap-3">
                                    <span class="text-2xl">👑</span>
                                    <span class="text-lg font-black text-amber-800 tracking-wide" x-text="teams[selectedTeamId] || ''"></span>
                                </div>

                                <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-xs text-red-800 font-semibold flex gap-2">
                                    <span class="flex-shrink-0">⚠️</span>
                                    <p>Ce choix est <strong>strictement définitif</strong>. Une fois validé, vous ne pourrez plus le modifier et aucun retour en arrière ne sera possible.</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                                <button 
                                    type="button" 
                                    @click="showConfirmModal = false"
                                    class="w-full sm:w-auto inline-flex justify-center rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-5 py-2.5 text-sm transition-all shadow-sm"
                                >
                                    Annuler
                                </button>
                                <button 
                                    type="button"
                                    @click="$refs.championForm.submit()"
                                    class="w-full sm:w-auto inline-flex justify-center rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold px-5 py-2.5 text-sm transition-all shadow-md hover:shadow-lg"
                                >
                                    Confirmer mon choix
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
