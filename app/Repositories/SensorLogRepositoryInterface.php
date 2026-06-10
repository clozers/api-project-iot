<?php

namespace App\Repositories;

use App\Models\SensorLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SensorLogRepositoryInterface
{
    /**
     * Create a new sensor log record.
     */
    public function create(array $data): SensorLog;

    /**
     * Get the latest sensor log.
     */
    public function getLatest(): ?SensorLog;

    /**
     * Get recent sensor log history for real-time line charts.
     */
    public function getHistory(int $limit = 20): Collection;

    /**
     * Get total count of sensor log records.
     */
    public function getTotalCount(): int;

    /**
     * Get the flame detection count (total records with flame_detected = true).
     */
    public function getFlameDetectionCount(): int;

    /**
     * Get statistics (average gas, max gas, flame detections) for today.
     */
    public function getDailyStats(): array;

    /**
     * Get statistics (average gas, max gas, flame detections) for this week (last 7 days).
     */
    public function getWeeklyStats(): array;

    /**
     * Get daily averages of gas levels for the last $days days for bar chart.
     */
    public function getDailyAveragesForChart(int $days = 7): Collection;

    /**
     * Get paginated sensor logs with search and sorting.
     */
    public function getPaginatedLogs(?string $search = null, string $sortField = 'created_at', string $sortOrder = 'desc', int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all sensor logs matching search for CSV export.
     */
    public function getAllLogsForExport(?string $search = null, string $sortField = 'created_at', string $sortOrder = 'desc'): Collection;
}
