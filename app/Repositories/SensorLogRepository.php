<?php

namespace App\Repositories;

use App\Models\SensorLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorLogRepository implements SensorLogRepositoryInterface
{
    /**
     * Create a new sensor log record.
     */
    public function create(array $data): SensorLog
    {
        return SensorLog::create([
            'gas_value' => $data['gas'],
            'flame_detected' => (bool)$data['flame'],
        ]);
    }

    /**
     * Get the latest sensor log.
     */
    public function getLatest(): ?SensorLog
    {
        return SensorLog::latest('id')->first();
    }

    /**
     * Get recent sensor log history for real-time line charts.
     */
    public function getHistory(int $limit = 20): Collection
    {
        // Get the latest records first, then reverse them so they are in chronological order
        return SensorLog::latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Get total count of sensor log records.
     */
    public function getTotalCount(): int
    {
        return SensorLog::count();
    }

    /**
     * Get the flame detection count (total records with flame_detected = true).
     */
    public function getFlameDetectionCount(): int
    {
        return SensorLog::where('flame_detected', true)->count();
    }

    /**
     * Get statistics (average gas, max gas, flame detections) for today.
     */
    public function getDailyStats(): array
    {
        $startOfToday = Carbon::today();

        $stats = SensorLog::where('created_at', '>=', $startOfToday)
            ->selectRaw('COALESCE(AVG(gas_value), 0) as avg_gas, COALESCE(MAX(gas_value), 0) as max_gas, COALESCE(SUM(CASE WHEN flame_detected = 1 THEN 1 ELSE 0 END), 0) as flame_count')
            ->first();

        return [
            'avg_gas' => round($stats->avg_gas, 1),
            'max_gas' => (int)$stats->max_gas,
            'flame_count' => (int)$stats->flame_count,
        ];
    }

    /**
     * Get statistics (average gas, max gas, flame detections) for this week (last 7 days).
     */
    public function getWeeklyStats(): array
    {
        $startOfWeek = Carbon::now()->subDays(7);

        $stats = SensorLog::where('created_at', '>=', $startOfWeek)
            ->selectRaw('COALESCE(AVG(gas_value), 0) as avg_gas, COALESCE(MAX(gas_value), 0) as max_gas, COALESCE(SUM(CASE WHEN flame_detected = 1 THEN 1 ELSE 0 END), 0) as flame_count')
            ->first();

        return [
            'avg_gas' => round($stats->avg_gas, 1),
            'max_gas' => (int)$stats->max_gas,
            'flame_count' => (int)$stats->flame_count,
        ];
    }

    /**
     * Get daily averages of gas levels for the last $days days for bar chart.
     */
    public function getDailyAveragesForChart(int $days = 7): Collection
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        // Query group by date, utilizing database-specific functions for MySQL
        return SensorLog::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as log_date, COALESCE(AVG(gas_value), 0) as avg_gas')
            ->groupBy('log_date')
            ->orderBy('log_date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->log_date)->format('Y-m-d'),
                    'label' => Carbon::parse($item->log_date)->format('M d'),
                    'avg_gas' => round($item->avg_gas, 1)
                ];
            });
    }

    /**
     * Get paginated sensor logs with search and sorting.
     */
    public function getPaginatedLogs(?string $search = null, string $sortField = 'created_at', string $sortOrder = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        // Sanitize sorting field to avoid SQL injection
        $allowedSortFields = ['id', 'gas_value', 'flame_detected', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $query = SensorLog::query();

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('gas_value', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
                
                if (strtolower($search) === 'fire' || strtolower($search) === 'flame' || strtolower($search) === 'yes' || strtolower($search) === 'true' || $search === '1') {
                    $q->orWhere('flame_detected', true);
                } elseif (strtolower($search) === 'safe' || strtolower($search) === 'no' || strtolower($search) === 'false' || $search === '0') {
                    $q->orWhere('flame_detected', false);
                }
            });
        }

        return $query->orderBy($sortField, $sortOrder)->paginate($perPage)->withQueryString();
    }

    /**
     * Get all sensor logs matching search for CSV export.
     */
    public function getAllLogsForExport(?string $search = null, string $sortField = 'created_at', string $sortOrder = 'desc'): Collection
    {
        // Sanitize sorting
        $allowedSortFields = ['id', 'gas_value', 'flame_detected', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $query = SensorLog::query();

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('gas_value', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
                
                if (strtolower($search) === 'fire' || strtolower($search) === 'flame' || strtolower($search) === 'yes' || strtolower($search) === 'true' || $search === '1') {
                    $q->orWhere('flame_detected', true);
                } elseif (strtolower($search) === 'safe' || strtolower($search) === 'no' || strtolower($search) === 'false' || $search === '0') {
                    $q->orWhere('flame_detected', false);
                }
            });
        }

        return $query->orderBy($sortField, $sortOrder)->get();
    }
}
