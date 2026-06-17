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
        $validated = $validator->validated();
        $this->sensorRepository->create($validated);

        // Check sensor data and send Telegram notification if status is dangerous or recovered
        $this->checkAndSendTelegramAlert((int)$validated['gas'], (bool)$validated['flame']);

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
        
        $isOnline = $diffSeconds <= 90; // Online if logged within 90 seconds (accommodating 60s safe-state upload interval)

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

    /**
     * Check sensor thresholds and send a throttled Telegram alert if necessary.
     * Only alerts on dangerous states: FIRE_DETECTED and GAS_LEAK.
     */
    private function checkAndSendTelegramAlert(int $gas, bool $flame): void
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            return;
        }

        // Only proceed if in a dangerous state
        if (!$flame && $gas <= 1500) {
            return;
        }

        $currentState = $flame ? 'FIRE_DETECTED' : 'GAS_LEAK';

        // Retrieve last alert state and time from Cache to prevent spamming
        $lastAlertState = Cache::get('tg_last_alert_state', 'SAFE');
        $lastAlertTime = Cache::get('tg_last_alert_time');

        $shouldAlert = false;

        // Alert immediately if state changed (e.g. GAS_LEAK -> FIRE_DETECTED)
        if ($currentState !== $lastAlertState) {
            $shouldAlert = true;
        }
        // Otherwise, repeat alert every 5 minutes while danger persists
        elseif (!$lastAlertTime || Carbon::parse($lastAlertTime)->diffInMinutes(Carbon::now()) >= 5) {
            $shouldAlert = true;
        }

        if ($shouldAlert) {
            if ($currentState === 'FIRE_DETECTED') {
                $message = "🔥 *PERINGATAN KRITIS: API TERDETEKSI!* 🔥\n\n"
                    . "Sensor Gas MQ-2: `{$gas}` ppm\n"
                    . "Sensor Api KY-026: *API TERDETEKSI!*\n\n"
                    . "⚠️ _Segera periksa lokasi dan ambil tindakan keselamatan!_";
            } else {
                $message = "⚠️ *PERINGATAN: KEBOCORAN GAS TERDETEKSI!* ⚠️\n\n"
                    . "Sensor Gas MQ-2: `{$gas}` ppm\n"
                    . "Sensor Api KY-026: Normal\n\n"
                    . "⚠️ _Kadar gas melebihi ambang batas aman (1500 ppm)._";
            }

            try {
                \Illuminate\Support\Facades\Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);

                // Update cache to reflect the sent alert
                Cache::put('tg_last_alert_state', $currentState);
                Cache::put('tg_last_alert_time', Carbon::now()->toDateTimeString());
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Telegram notification: " . $e->getMessage());
            }
        }
    }
}
