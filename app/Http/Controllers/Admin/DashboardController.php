<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\Season;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'predictions' => Prediction::count(),
            'seasons' => Season::count(),
            'games' => Game::count(),
            'active_season' => Season::where('is_active', true)->first(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
