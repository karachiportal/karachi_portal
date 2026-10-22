<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\PriceItem;
use App\Models\PriceUpdateLog;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'       => Complaint::count(),
            'pending'     => Complaint::where('status','pending')->count(),
            'in_progress' => Complaint::where('status','in_progress')->count(),
            'resolved'    => Complaint::where('status','resolved')->count(),
            'rejected'    => Complaint::where('status','rejected')->count(),
            'today'       => Complaint::whereDate('created_at', today())->count(),
            'this_month'  => Complaint::whereMonth('created_at', now()->month)->count(),
        ];

        $recentComplaints = Complaint::with('user')
            ->orderByDesc('created_at')->limit(8)->get();

        $topItems = Complaint::selectRaw('item_name, COUNT(*) as total')
            ->groupBy('item_name')->orderByDesc('total')->limit(6)->get();

        $totalUsers      = User::where('role','citizen')->count();
        $totalPriceItems = PriceItem::where('is_active', true)->count();
        $pendingCount    = $stats['pending'];

        // Price update logs
        $recentPriceUpdates = PriceUpdateLog::with(['item.category', 'updater'])
            ->orderByDesc('created_at')->limit(10)->get();

        $todayUpdates = PriceUpdateLog::whereDate('created_at', today())->count();
        $totalUpdates = PriceUpdateLog::count();

        $monthlyLabels = [];
        $monthlyData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlyData[]   = Complaint::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)->count();
        }

        return view('dashboard.index', compact(
            'stats','recentComplaints','topItems',
            'totalUsers','totalPriceItems','pendingCount',
            'monthlyLabels','monthlyData',
            'recentPriceUpdates','todayUpdates','totalUpdates'
        ));
    }
}
