<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Form;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $todayCount = Transaction::whereDate('created_at', now()->toDateString())->count();
        $inProgressCount = Transaction::where('status', 'in_progress')->count();
        $completedToday = Transaction::where('status', 'completed')
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $todayTonnage = Transaction::where('status', 'completed')
            ->whereDate('created_at', now()->toDateString())
            ->sum('net_weight') / 1000;

        $activeFormsCount = Form::where('is_active', true)->count();
        $operatorsCount = User::role('Scale Operator')->count();

        $recentTransactions = Transaction::with(['form', 'creator'])
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'todayCount',
            'inProgressCount',
            'completedToday',
            'todayTonnage',
            'activeFormsCount',
            'operatorsCount',
            'recentTransactions'
        ));
    }
}
