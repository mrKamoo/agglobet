<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

use App\Models\ChatMessageReaction;

class ChatMessageController extends Controller
{
    public function index()
    {
        $messages = ChatMessage::with(['user:id,name,is_admin', 'reactions'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

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
