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
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');

        // Base queries with permission scopes
        $historyQuery = ConstancyGeneralHistory::query();
        $eventQuery = Event::query();
        $configQuery = DocumentConfiguration::query();

        if (!$isAdmin) {
            $historyQuery->visibleTo($user);
            $eventQuery->visibleTo($user);
            $configQuery->visibleTo($user);
        }

        // KPIs
        $totalCertificates = (clone $historyQuery)->sum('procesados_exitosos');
        $activeEvents = (clone $eventQuery)->where('is_active', true)->count();
        $totalTemplates = $configQuery->count();
        $totalUsers = $isAdmin ? User::count() : 1;
        $inactiveEvents = (clone $eventQuery)->where('is_active', false)->count();
        $totalBatches = (clone $historyQuery)->count();
        
        $lastBatchAt = (clone $historyQuery)->latest()->value('created_at');
        
        $certificatesToday = (clone $historyQuery)->whereDate('created_at', now()->toDateString())
            ->sum('procesados_exitosos');
            
        $certificatesThisMonth = (clone $historyQuery)->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->sum('procesados_exitosos');

        // Recent Activity
        $recentBatches = (clone $historyQuery)->with(['user', 'documentConfiguration'])
            ->latest()
            ->take(5)
            ->get();

        // --- Chart Data ---

        // 1. Monthly Trends (Last 6 months)
        $monthlyStats = (clone $historyQuery)->select(
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
        $eventStats = (clone $historyQuery)
            ->join('document_configurations', 'constancy_general_history.document_configuration_id', '=', 'document_configurations.id')
            ->join('events', 'document_configurations.event_id', '=', 'events.id')
            ->select('events.name', \Illuminate\Support\Facades\DB::raw('SUM(constancy_general_history.procesados_exitosos) as total'))
            ->groupBy('events.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $eventLabels = $eventStats->pluck('name');
        $eventCounts = $eventStats->pluck('total');

        // 3. Global Success Rate
        $totalRecords = (clone $historyQuery)->sum('total_registros');
        $totalSuccess = (clone $historyQuery)->sum('procesados_exitosos');
        $globalSuccessRate = $totalRecords > 0 ? round(($totalSuccess / $totalRecords) * 100, 1) : 0;

        $last30Stats = (clone $historyQuery)->where('created_at', '>=', now()->subDays(30))
            ->select(
                \Illuminate\Support\Facades\DB::raw('SUM(total_registros) as total'),
                \Illuminate\Support\Facades\DB::raw('SUM(procesados_exitosos) as success')
            )->first();
            
        $last30SuccessRate = ($last30Stats && $last30Stats->total > 0) 
            ? round(($last30Stats->success / $last30Stats->total) * 100, 1) 
            : 0;

        // 4. Certificates by Event Type
        $eventTypeStats = (clone $historyQuery)
            ->join('document_configurations', 'constancy_general_history.document_configuration_id', '=', 'document_configurations.id')
            ->join('events', 'document_configurations.event_id', '=', 'events.id')
            ->select('events.type', \Illuminate\Support\Facades\DB::raw('SUM(constancy_general_history.procesados_exitosos) as total'))
            ->groupBy('events.type')
            ->orderByDesc('total')
            ->get();

        $eventTypeLabels = $eventTypeStats->pluck('type');
        $eventTypeCounts = $eventTypeStats->pluck('total');

        // 5. Failure Analysis
        $failureStats = (clone $historyQuery)->select('created_at', 'procesados_fallidos', 'total_registros')
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
