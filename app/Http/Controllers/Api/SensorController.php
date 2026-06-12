<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SensorLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SensorController extends Controller
{
    protected $sensorRepository;

    public function __construct(SensorLogRepositoryInterface $sensorRepository)
    {
        $this->sensorRepository = $sensorRepository;
    }

    /**
     * Store a new sensor log entry.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate payload
        $validator = Validator::make($request->all(), [
            'gas' => 'required|integer|min:0',
            'flame' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Store data using the repository
        $this->sensorRepository->create($validator->validated());

        // Set session start time if device is newly online / cache has expired
        if (!Cache::has('session_start')) {
            Cache::put('session_start', Carbon::now()->toDateTimeString());
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get the latest sensor reading and system status.
     */
    public function latest(): JsonResponse
    {
        $latestLog = $this->sensorRepository->getLatest();
        $systemState = $this->calculateSystemState($latestLog);
        $deviceState = $this->calculateDeviceState($latestLog);

        return response()->json([
            'latest_reading' => $latestLog ? [
                'id' => $latestLog->id,
                'gas_value' => $latestLog->gas_value,
                'flame_detected' => $latestLog->flame_detected,
                'timestamp' => $latestLog->created_at->toDateTimeString(),
            ] : null,
            'system_status' => $systemState['status'],
            'status_color' => $systemState['color'],
            'device_status' => $deviceState['status'],
            'device_uptime' => $deviceState['uptime'],
        ]);
    }

    /**
     * Get recent sensor log history.
     */
    public function history(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 20);
        $logs = $this->sensorRepository->getHistory((int)$limit);

        $formattedLogs = $logs->reverse()->values()->map(function($log) {
            return [
                'id' => $log->id,
                'gas_value' => $log->gas_value,
                'flame_detected' => $log->flame_detected,
                'timestamp' => $log->created_at->toDateTimeString(),
            ];
        });

        return response()->json($formattedLogs);
    }

    /**
     * Determine current system state based on the latest readings.
     */
    private function calculateSystemState(?object $latestLog): array
    {
        if (!$latestLog) {
            return [
                'status' => 'UNKNOWN',
                'color' => 'gray'
            ];
        }

        $gasValue = $latestLog->gas_value;
        $flameDetected = $latestLog->flame_detected;

        if ($flameDetected) {
            return [
                'status' => 'FIRE DETECTED',
                'color' => 'red'
            ];
        }

        if ($gasValue > 1500) {
            return [
                'status' => 'GAS LEAK',
                'color' => 'orange'
            ];
        }

        return [
            'status' => 'SAFE',
            'color' => 'green'
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
        
        $isOnline = $diffSeconds <= 15; // Online if logged within 15 seconds

        if ($isOnline) {
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

        return [
            'online' => false,
            'status' => 'OFFLINE',
            'last_seen' => $lastSeen->diffForHumans(),
            'uptime' => '0m (Offline)'
        ];
    }
}
