<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\DocumentConfiguration;
use App\Models\ConstancyGeneralHistory;

class DashboardController extends Controller
{
    public function index()
    {
        // KPIs
        $totalCertificates = ConstancyGeneralHistory::sum('procesados_exitosos');
        $activeEvents = Event::where('is_active', true)->count();
        $totalTemplates = DocumentConfiguration::count();
        $totalUsers = User::count();
        $inactiveEvents = Event::where('is_active', false)->count();
        $totalBatches = ConstancyGeneralHistory::count();
        $lastBatchAt = ConstancyGeneralHistory::latest()->value('created_at');
        $certificatesToday = ConstancyGeneralHistory::whereDate('created_at', now()->toDateString())
            ->sum('procesados_exitosos');
        $certificatesThisMonth = ConstancyGeneralHistory::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->sum('procesados_exitosos');

        // Recent Activity
        $recentBatches = ConstancyGeneralHistory::with(['user', 'documentConfiguration'])
            ->latest()
            ->take(5)
            ->get();

        // --- Chart Data ---

        // 1. Monthly Trends (Last 6 months)
        // Group by Year-Month and sum successful processed
        $monthlyStats = ConstancyGeneralHistory::select(
            \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            \Illuminate\Support\Facades\DB::raw('SUM(procesados_exitosos) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = $monthlyStats->pluck('month')->map(function ($m) {
            return \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y');
        });
        $monthlyCounts = $monthlyStats->pluck('total');

        // 2. Events Distribution
        // Join history -> config -> event to count certificates per event
        $eventStats = ConstancyGeneralHistory::join('document_configurations', 'constancy_general_history.document_configuration_id', '=', 'document_configurations.id')
            ->join('events', 'document_configurations.event_id', '=', 'events.id')
            ->select('events.name', \Illuminate\Support\Facades\DB::raw('SUM(constancy_general_history.procesados_exitosos) as total'))
            ->groupBy('events.name')
            ->orderByDesc('total')
            ->take(5) // Top 5 events
            ->get();

        $eventLabels = $eventStats->pluck('name');
        $eventCounts = $eventStats->pluck('total');

        // 3. Global Success Rate
        $totalRecords = ConstancyGeneralHistory::sum('total_registros');
        $totalSuccess = ConstancyGeneralHistory::sum('procesados_exitosos');
        $globalSuccessRate = $totalRecords > 0 ? round(($totalSuccess / $totalRecords) * 100, 1) : 0;

        $last30Records = ConstancyGeneralHistory::where('created_at', '>=', now()->subDays(30))
            ->sum('total_registros');
        $last30Success = ConstancyGeneralHistory::where('created_at', '>=', now()->subDays(30))
            ->sum('procesados_exitosos');
        $last30SuccessRate = $last30Records > 0 ? round(($last30Success / $last30Records) * 100, 1) : 0;

        // 4. Certificates by Event Type
        $eventTypeStats = ConstancyGeneralHistory::join('document_configurations', 'constancy_general_history.document_configuration_id', '=', 'document_configurations.id')
            ->join('events', 'document_configurations.event_id', '=', 'events.id')
            ->select('events.type', \Illuminate\Support\Facades\DB::raw('SUM(constancy_general_history.procesados_exitosos) as total'))
            ->groupBy('events.type')
            ->orderByDesc('total')
            ->get();

        $eventTypeLabels = $eventTypeStats->pluck('type');
        $eventTypeCounts = $eventTypeStats->pluck('total');

        // 5. Failure Analysis (Top 5 recent batches with failures)
        $failureStats = ConstancyGeneralHistory::select('created_at', 'procesados_fallidos', 'total_registros')
            ->where('procesados_fallidos', '>', 0)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->created_at->format('d M H:i'),
                    'failed' => $item->procesados_fallidos,
                    'total' => $item->total_registros
                ];
            });

        return view('dashboard', compact(
            'totalCertificates',
            'activeEvents',
            'totalTemplates',
            'totalUsers',
            'inactiveEvents',
            'totalBatches',
            'lastBatchAt',
            'certificatesToday',
            'certificatesThisMonth',
            'recentBatches',
            'months',
            'monthlyCounts',
            'eventLabels',
            'eventCounts',
            'globalSuccessRate',
            'last30SuccessRate',
            'eventTypeLabels',
            'eventTypeCounts',
            'failureStats'
        ));
    }
}
