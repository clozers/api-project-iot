<?php

namespace App\Http\Controllers;

use App\Repositories\SensorLogRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class SensorLogController extends Controller
{
    protected $sensorRepository;

    public function __construct(SensorLogRepositoryInterface $sensorRepository)
    {
        $this->sensorRepository = $sensorRepository;
    }

    /**
     * Display a listing of the sensor logs.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'created_at');
        $order = $request->query('order', 'desc');

        $logs = $this->sensorRepository->getPaginatedLogs($search, $sortBy, $order, 15);

        return view('logs', compact('logs', 'search', 'sortBy', 'order'));
    }

    /**
     * Export the sensor logs to a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'created_at');
        $order = $request->query('order', 'desc');

        $logs = $this->sensorRepository->getAllLogsForExport($search, $sortBy, $order);

        $filename = 'smart_safety_sensor_logs_' . Carbon::now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, ['ID', 'Gas Value (ppm)', 'Flame Status', 'Timestamp']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->gas_value,
                    $log->flame_detected ? 'FIRE DETECTED' : 'SAFE',
                    Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
