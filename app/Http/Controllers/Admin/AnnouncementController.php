<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        if ($request->input('action') === 'delete') {
            Announcement::query()->delete();
            return redirect()->route('dashboard')->with('status', 'broadcast-deleted');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        Announcement::query()->delete();

        Announcement::create([
            'content' => $validated['content'],
            'is_active' => $request->has('is_active'),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('dashboard')->with('status', 'broadcast-updated');
    }
}
