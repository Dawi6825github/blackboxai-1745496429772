<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Get active rounds
        $activeRounds = Round::where('status', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->count();

        // Get total users
        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('status', true)->count();

        // Get betting statistics
        $totalBets = Bet::count();
        $activeBets = Bet::where('status', 'active')->count();
        $wonBets = Bet::where('status', 'won')->count();
        $lostBets = Bet::where('status', 'lost')->count();

        // Get revenue statistics
        $totalBetAmount = Bet::sum('amount');
        $totalWinnings = Bet::where('status', 'won')->sum('winnings');
        $revenue = $totalBetAmount - $totalWinnings;

        // Get recent bets
        $recentBets = Bet::with(['user', 'round', 'pattern'])
            ->orderBy('placed_at', 'desc')
            ->limit(10)
            ->get();

        // Get daily bet statistics for the last 7 days
        $dailyStats = Bet::select(
            DB::raw('DATE(placed_at) as date'),
            DB::raw('COUNT(*) as total_bets'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('SUM(CASE WHEN status = "won" THEN winnings ELSE 0 END) as total_winnings')
        )
            ->where('placed_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'activeRounds' => $activeRounds,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'totalBets' => $totalBets,
            'activeBets' => $activeBets,
            'wonBets' => $wonBets,
            'lostBets' => $lostBets,
            'totalBetAmount' => $totalBetAmount,
            'totalWinnings' => $totalWinnings,
            'revenue' => $revenue,
            'recentBets' => $recentBets,
            'dailyStats' => $dailyStats,
        ]);
    }
}
