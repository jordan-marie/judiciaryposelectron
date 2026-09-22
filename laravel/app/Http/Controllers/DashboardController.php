<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Form;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $todayCount = Transaction::whereDate('created_at', $today)->count();
        $completedToday = Transaction::whereDate('created_at', $today)->where('status', 'completed')->count();
        $inProgressCount = Transaction::where('status', 'in_progress')->count();
        $totalNetWeightToday = Transaction::whereDate('created_at', $today)->where('status', 'completed')->sum('net_weight');

        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $activeFormsCount = Form::where('is_active', true)->count();
        $operatorsCount = User::role('Scale Operator')->count();

        return view('dashboard.index', compact(
            'todayCount',
            'completedToday',
            'inProgressCount',
            'totalNetWeightToday',
            'recentTransactions',
            'activeFormsCount',
            'operatorsCount'
        ));
    }
}
