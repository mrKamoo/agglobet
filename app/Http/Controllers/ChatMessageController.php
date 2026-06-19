<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class ChatMessageController extends Controller
{
    public function index()
    {
        $messages = ChatMessage::with('user:id,name,is_admin')
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

        $message->load('user:id,name,is_admin');

        return response()->json($message, 201);
    }
}
