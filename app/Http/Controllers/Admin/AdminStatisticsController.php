<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use App\Models\Sanction;
use Illuminate\Support\Facades\DB;

class AdminStatisticsController extends Controller
{
    public function index()
    {
        $currentYear = date('n') >= 8 ? date('Y') . '-' . (date('Y') + 1) : (date('Y') - 1) . '-' . date('Y');

        // ── Metrics ────────────────────────────────────────────────────────────
        $totalViolations = Violation::where('school_year', $currentYear)->count();

        $activeSanctions = Sanction::whereHas('violation', fn($q) => $q->where('school_year', $currentYear))
            ->where('is_active', true)->count();

        $totalWarnings = Sanction::whereHas('violation', fn($q) => $q->where('school_year', $currentYear))
            ->where('sanction_type', 'Warning')->count();

        $totalSevere = Sanction::whereHas('violation', fn($q) => $q->where('school_year', $currentYear))
            ->whereIn('sanction_type', ['Suspended', 'Revoked'])->count();

        // ── Violation Type Breakdown (Doughnut) ────────────────────────────────
        $typeBreakdown = Violation::select('violation_type', DB::raw('count(*) as total'))
            ->where('school_year', $currentYear)
            ->groupBy('violation_type')
            ->get()
            ->mapWithKeys(fn($item) => [ucwords(str_replace('_', ' ', $item->violation_type)) => $item->total]);

        // ── Monthly Trend (Bar) ────────────────────────────────────────────────
        $monthlyViolationsRaw = Violation::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->where('school_year', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()->pluck('total', 'month')->toArray();

        $monthsOrder  = [8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7];
        $monthlyData  = [];
        $monthlyLabels = [];
        foreach ($monthsOrder as $m) {
            $monthlyLabels[] = \DateTime::createFromFormat('!m', $m)->format('M');
            $monthlyData[]   = $monthlyViolationsRaw[$m] ?? 0;
        }

        // ── Violations by Location (Horizontal Bar) ────────────────────────────
        // Groups on the first ~40 characters of location_notes so similar areas merge.
        $locationRaw = Violation::select(
                DB::raw("TRIM(SUBSTRING(location_notes, 1, 45)) as area"),
                'violation_type',
                DB::raw('count(*) as total')
            )
            ->where('school_year', $currentYear)
            ->whereNotNull('location_notes')
            ->where('location_notes', '!=', '')
            ->groupBy('area', 'violation_type')
            ->orderByDesc('total')
            ->get();

        // Build a pivot: area → [ violation_type => count ]
        $locationLabels = [];
        $locationPivot  = [];   // [ area => [ type => count ] ]
        $allTypes       = [];

        foreach ($locationRaw as $row) {
            $area = $row->area;
            $type = ucwords(str_replace('_', ' ', $row->violation_type));
            if (!in_array($area, $locationLabels)) {
                $locationLabels[] = $area;
            }
            $allTypes[$type] = true;
            $locationPivot[$area][$type] = ($locationPivot[$area][$type] ?? 0) + $row->total;
        }

        // Sort areas by total descending, cap at top-10
        usort($locationLabels, function ($a, $b) use ($locationPivot) {
            return array_sum($locationPivot[$b] ?? []) <=> array_sum($locationPivot[$a] ?? []);
        });
        $locationLabels = array_slice($locationLabels, 0, 10);
        $allTypes       = array_keys($allTypes);

        // Stacked dataset per violation type
        $palette = ['#800000','#ef4444','#f59e0b','#3b82f6','#10b981','#6366f1','#8b5cf6','#14b8a6','#f97316','#ec4899'];
        $locationDatasets = [];
        foreach ($allTypes as $i => $type) {
            $values = [];
            foreach ($locationLabels as $area) {
                $values[] = $locationPivot[$area][$type] ?? 0;
            }
            $locationDatasets[] = [
                'label'           => $type,
                'data'            => $values,
                'backgroundColor' => $palette[$i % count($palette)],
                'borderRadius'    => 3,
            ];
        }

        return view('admin.statistics.index', compact(
            'currentYear',
            'totalViolations',
            'activeSanctions',
            'totalWarnings',
            'totalSevere',
            'typeBreakdown',
            'monthlyLabels',
            'monthlyData',
            'locationLabels',
            'locationDatasets'
        ));
    }
}
