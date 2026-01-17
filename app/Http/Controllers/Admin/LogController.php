<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\Prediction;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display the logs page.
     */
    public function index()
    {
        // Logs de synchronisation API
        $apiLogs = ApiLog::orderBy('created_at', 'desc')->paginate(10, ['*'], 'api_page');

        // Logs des pronostics (tous les utilisateurs)
        $predictions = Prediction::with(['user', 'game.homeTeam', 'game.awayTeam'])
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'pred_page');

        return view('admin.logs.index', compact('apiLogs', 'predictions'));
    }
}
