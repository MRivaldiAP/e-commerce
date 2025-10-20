<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $startOfYear = $today->copy()->startOfYear();
        $endOfYear = $today->copy()->endOfYear();

        $dailyVisits = LandingPageVisit::query()
            ->whereDate('date', $today)
            ->orderByDesc('total_visits')
            ->get();

        $monthlyVisits = LandingPageVisit::query()
            ->select('page',
                DB::raw('SUM(total_visits) as total_visits'),
                DB::raw('SUM(unique_visits) as unique_visits'),
                DB::raw('SUM(primary_visits) as primary_visits'),
                DB::raw('SUM(secondary_visits) as secondary_visits')
            )
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->groupBy('page')
            ->orderByDesc('total_visits')
            ->get();

        $yearlyVisits = LandingPageVisit::query()
            ->select('page',
                DB::raw('SUM(total_visits) as total_visits'),
                DB::raw('SUM(unique_visits) as unique_visits'),
                DB::raw('SUM(primary_visits) as primary_visits'),
                DB::raw('SUM(secondary_visits) as secondary_visits')
            )
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->groupBy('page')
            ->orderByDesc('total_visits')
            ->get();

        $dailyTotals = $this->aggregateTotals($dailyVisits);
        $monthlyTotals = $this->aggregateTotals($monthlyVisits);
        $yearlyTotals = $this->aggregateTotals($yearlyVisits);

        return view('admin.dashboard.index', [
            'dailyVisits' => $dailyVisits,
            'monthlyVisits' => $monthlyVisits,
            'yearlyVisits' => $yearlyVisits,
            'dailyTotals' => $dailyTotals,
            'monthlyTotals' => $monthlyTotals,
            'yearlyTotals' => $yearlyTotals,
            'today' => $today,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'startOfYear' => $startOfYear,
            'endOfYear' => $endOfYear,
        ]);
    }

    private function aggregateTotals($collection): array
    {
        return [
            'total_visits' => (int) $collection->sum('total_visits'),
            'unique_visits' => (int) $collection->sum('unique_visits'),
            'primary_visits' => (int) $collection->sum('primary_visits'),
            'secondary_visits' => (int) $collection->sum('secondary_visits'),
        ];
    }
}
