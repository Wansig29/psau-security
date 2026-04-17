<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use App\Models\Sanction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticsController extends Controller
{
    public function index()
    {
        $currentYear = date('n') >= 8 ? date('Y') . '-' . (date('Y') + 1) : (date('Y') - 1) . '-' . date('Y');

        // Metrics
        $totalViolations = Violation::where('school_year', $currentYear)->count();
        $activeSanctions = Sanction::whereHas('violation', function($q) use ($currentYear) {
            $q->where('school_year', $currentYear);
        })->where('is_active', true)->count();
        
        $totalWarnings = Sanction::whereHas('violation', function($q) use ($currentYear) {
            $q->where('school_year', $currentYear);
        })->where('sanction_type', 'Warning')->count();
        
        $totalSevere = Sanction::whereHas('violation', function($q) use ($currentYear) {
            $q->where('school_year', $currentYear);
        })->whereIn('sanction_type', ['Suspended', 'Revoked'])->count();

        // Type Breakdown
        $typeBreakdown = Violation::select('violation_type', DB::raw('count(*) as total'))
            ->where('school_year', $currentYear)
            ->groupBy('violation_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [ucwords(str_replace('_', ' ', $item->violation_type)) => $item->total];
            });

        // Monthly Breakdown
        $monthlyViolationsRaw = Violation::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->where('school_year', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Align chart from August to July
        $monthsOrder = [8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7];
        $monthlyData = [];
        $monthlyLabels = [];
        
        foreach ($monthsOrder as $m) {
            $dateObj   = \DateTime::createFromFormat('!m', $m);
            $monthName = $dateObj->format('M');
            
            $monthlyLabels[] = $monthName;
            $monthlyData[]   = $monthlyViolationsRaw[$m] ?? 0;
        }

        return view('admin.statistics.index', compact(
            'currentYear',
            'totalViolations',
            'activeSanctions',
            'totalWarnings',
            'totalSevere',
            'typeBreakdown',
            'monthlyLabels',
            'monthlyData'
        ));
    }
}
