<?php

namespace App\Http\Controllers;

use App\Repositories\SensorLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $sensorRepository;

    public function __construct(SensorLogRepositoryInterface $sensorRepository)
    {
        $this->sensorRepository = $sensorRepository;
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        $latestLog = $this->sensorRepository->getLatest();
        
        // Load initial state for Blade (first load)
        $systemState = $this->calculateSystemState($latestLog);
        $deviceInfo = $this->calculateDeviceState($latestLog);
        $dailyStats = $this->sensorRepository->getDailyStats();
        $weeklyStats = $this->sensorRepository->getWeeklyStats();
        $totalCount = $this->sensorRepository->getTotalCount();
        $flameCount = $this->sensorRepository->getFlameDetectionCount();
        
        // Initial charts data
        $historyLogs = $this->sensorRepository->getHistory(20);
        $dailyAverages = $this->sensorRepository->getDailyAveragesForChart(7);

        // Map recent logs for table preview
        $recentLogs = $historyLogs->reverse()->take(10)->values();

        return view('dashboard', compact(
            'latestLog',
            'systemState',
            'deviceInfo',
            'dailyStats',
            'weeklyStats',
            'totalCount',
            'flameCount',
            'recentLogs',
            'historyLogs',
            'dailyAverages'
        ));
    }

    /**
     * Get real-time JSON updates for the dashboard.
     */
    public function data(): JsonResponse
    {
        $latestLog = $this->sensorRepository->getLatest();
        
        $systemState = $this->calculateSystemState($latestLog);
        $deviceInfo = $this->calculateDeviceState($latestLog);
        $dailyStats = $this->sensorRepository->getDailyStats();
        $weeklyStats = $this->sensorRepository->getWeeklyStats();
        $totalCount = $this->sensorRepository->getTotalCount();
        $flameCount = $this->sensorRepository->getFlameDetectionCount();
        
        $historyLogs = $this->sensorRepository->getHistory(20);
        $dailyAverages = $this->sensorRepository->getDailyAveragesForChart(7);
        
        // Format charts for JSON response
        $lineChart = [
            'labels' => $historyLogs->map(fn($log) => Carbon::parse($log->created_at)->format('H:i:s'))->toArray(),
            'gas_values' => $historyLogs->pluck('gas_value')->toArray(),
            'flame_events' => $historyLogs->map(fn($log) => $log->flame_detected ? 1 : 0)->toArray(),
        ];

        $barChart = [
            'labels' => $dailyAverages->pluck('label')->toArray(),
            'averages' => $dailyAverages->pluck('avg_gas')->toArray(),
        ];

        // Format recent logs list (last 10)
        $recentLogs = $historyLogs->reverse()->take(10)->values()->map(function($log) {
            return [
                'id' => $log->id,
                'gas_value' => $log->gas_value,
                'flame_detected' => $log->flame_detected,
                'timestamp' => Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
                'time_ago' => Carbon::parse($log->created_at)->diffForHumans(),
            ];
        });

        return response()->json([
            'latest_log' => $latestLog ? [
                'gas_value' => $latestLog->gas_value,
                'flame_detected' => $latestLog->flame_detected,
                'timestamp' => Carbon::parse($latestLog->created_at)->format('Y-m-d H:i:s'),
            ] : null,
            'system_state' => $systemState,
            'device_info' => $deviceInfo,
            'stats' => [
                'total_records' => $totalCount,
                'flame_count' => $flameCount,
                'daily' => $dailyStats,
                'weekly' => $weeklyStats,
            ],
            'recent_logs' => $recentLogs,
            'charts' => [
                'line' => $lineChart,
                'bar' => $barChart,
            ]
        ]);
    }

    /**
     * Determine current system state based on the latest readings.
     */
    private function calculateSystemState(?object $latestLog): array
    {
        if (!$latestLog) {
            return [
                'status' => 'UNKNOWN',
                'color' => 'gray',
                'class' => 'bg-slate-800 text-slate-400 border-slate-700',
                'message' => 'No sensor data available.'
            ];
        }

        $gasValue = $latestLog->gas_value;
        $flameDetected = $latestLog->flame_detected;

        if ($flameDetected) {
            return [
                'status' => 'FIRE DETECTED',
                'color' => 'red',
                'class' => 'bg-red-950/40 text-red-400 border-red-500/50 pulse-border',
                'message' => 'CRITICAL EMERGENCY: Fire activity detected by the KY-026 sensor!'
            ];
        }

        if ($gasValue > 1500) {
            return [
                'status' => 'GAS LEAK',
                'color' => 'orange',
                'class' => 'bg-amber-950/40 text-amber-400 border-amber-500/50 animate-pulse',
                'message' => 'WARNING: Elevated MQ-2 gas sensor level detected above safety threshold!'
            ];
        }

        return [
            'status' => 'SAFE',
            'color' => 'green',
            'class' => 'bg-emerald-950/30 text-emerald-400 border-emerald-500/40',
            'message' => 'Monitoring active. All environment levels are within safe parameters.'
        ];
    }

    /**
     * Compute device online status and session uptime.
     */
    private function calculateDeviceState(?object $latestLog): array
    {
        if (!$latestLog) {
            return [
                'online' => false,
                'status' => 'OFFLINE',
                'last_seen' => 'Never',
                'uptime' => '0m (Offline)'
            ];
        }

        $now = Carbon::now();
        $lastSeen = Carbon::parse($latestLog->created_at);
        $diffSeconds = $now->diffInSeconds($lastSeen, true);
        
        $isOnline = $diffSeconds <= 90; // Online if logged within 90 seconds (accommodating 60s safe-state upload interval)

        if ($isOnline) {
            // Retrieve session start from cache
            $sessionStartStr = Cache::get('session_start');
            if ($sessionStartStr) {
                $sessionStart = Carbon::parse($sessionStartStr);
                $diff = $now->diff($sessionStart);
                
                $parts = [];
                if ($diff->d > 0) $parts[] = $diff->d . 'd';
                if ($diff->h > 0) $parts[] = $diff->h . 'h';
                if ($diff->i > 0) $parts[] = $diff->i . 'm';
                if ($diff->s > 0 && count($parts) < 2) $parts[] = $diff->s . 's';
                
                $uptime = implode(' ', $parts);
                if (empty($uptime)) {
                    $uptime = 'Just started';
                }
            } else {
                $uptime = 'Calculating...';
            }

            return [
                'online' => true,
                'status' => 'ONLINE',
                'last_seen' => $diffSeconds === 0 ? 'Just now' : $diffSeconds . 's ago',
                'uptime' => $uptime
            ];
        }

        // Clear session cache if offline
        Cache::forget('session_start');

        return [
            'online' => false,
            'status' => 'OFFLINE',
            'last_seen' => $lastSeen->diffForHumans(),
            'uptime' => '0m (Offline)'
        ];
    }
}
