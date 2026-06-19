<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

use App\Models\ChatMessageReaction;

use App\Models\User;
use App\Models\Prediction;
use Illuminate\Support\Facades\DB;

class ChatMessageController extends Controller
{
    public function index()
    {
        // Calculer les points et rangs de tous les utilisateurs en une seule requête
        $usersStats = User::where('exclude_from_leaderboard', false)
            ->leftJoin('predictions', 'users.id', '=', 'predictions.user_id')
            ->select('users.id', DB::raw('COALESCE(SUM(predictions.points_earned), 0) as total_points'))
            ->groupBy('users.id')
            ->orderByDesc('total_points')
            ->get();

        $rankedUsers = [];
        foreach ($usersStats as $index => $stat) {
            $rankedUsers[$stat->id] = [
                'points' => (int) $stat->total_points,
                'rank' => $index + 1
            ];
        }

        $messages = ChatMessage::with(['user:id,name,is_admin', 'reactions'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        // Injecter les stats dans le profil de chaque auteur de message
        foreach ($messages as $message) {
            if ($message->user) {
                if (isset($rankedUsers[$message->user->id])) {
                    $message->user->points = $rankedUsers[$message->user->id]['points'];
                    $message->user->rank = $rankedUsers[$message->user->id]['rank'];
                } else {
                    $message->user->points = 0;
                    $message->user->rank = count($rankedUsers) + 1;
                }
            }
        }

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $message->load(['user:id,name,is_admin', 'reactions']);

        // Calculer les stats de l'utilisateur ayant posté pour la réponse immédiate
        if ($message->user) {
            $totalPoints = (int) Prediction::where('user_id', $message->user->id)->sum('points_earned');
            $rank = User::where('exclude_from_leaderboard', false)
                ->leftJoin('predictions', 'users.id', '=', 'predictions.user_id')
                ->select('users.id', DB::raw('COALESCE(SUM(predictions.points_earned), 0) as total_points'))
                ->groupBy('users.id')
                ->having('total_points', '>', $totalPoints)
                ->get()
                ->count() + 1;

            $message->user->points = $totalPoints;
            $message->user->rank = $rank;
        }

        return response()->json($message, 201);
    }

    public function react(Request $request, ChatMessage $chatMessage)
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:50',
        ]);

        $userId = Auth::id();
        $emoji = $validated['emoji'];

        // Trouver si la réaction existe déjà
        $reaction = ChatMessageReaction::where('chat_message_id', $chatMessage->id)
            ->where('user_id', $userId)
            ->where('emoji', $emoji)
            ->first();

        if ($reaction) {
            // Si elle existe, on l'enlève (toggle off)
            $reaction->delete();
        } else {
            // Sinon on la crée (toggle on)
            ChatMessageReaction::create([
                'chat_message_id' => $chatMessage->id,
                'user_id' => $userId,
                'emoji' => $emoji,
            ]);
        }

        // Retourner l'ensemble des réactions mises à jour pour ce message
        return response()->json($chatMessage->reactions()->get());
    }
}
