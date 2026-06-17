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
     * Export the sensor logs to a well-formatted CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $search  = $request->query('search');
        $sortBy  = $request->query('sort_by', 'created_at');
        $order   = $request->query('order', 'desc');

        // Additional export-specific filters
        $dateFrom = $request->query('date_from') ?: null;
        $dateTo   = $request->query('date_to') ?: null;
        $status   = $request->query('status') ?: null;   // 'safe', 'gas_leak', 'fire'
        $gasMin   = $request->query('gas_min') !== null && $request->query('gas_min') !== '' ? (int) $request->query('gas_min') : null;
        $gasMax   = $request->query('gas_max') !== null && $request->query('gas_max') !== '' ? (int) $request->query('gas_max') : null;

        $logs = $this->sensorRepository->getAllLogsForExport($search, $sortBy, $order, $dateFrom, $dateTo, $status, $gasMin, $gasMax);

        $exportedAt = Carbon::now();
        $filename   = 'smart_safety_logs_' . $exportedAt->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($logs, $exportedAt, $search, $dateFrom, $dateTo, $status, $gasMin, $gasMax) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // ─── Report Header ───────────────────────────────────────────────────────
            $statusLabel = match($status) {
                'fire'     => 'Fire Detected Only',
                'gas_leak' => 'Gas Leak Only',
                'safe'     => 'Safe Only',
                default    => '-',
            };

            fputcsv($file, ['SMART SAFETY - IoT Sensor Log Report']);
            fputcsv($file, ['Generated At',        $exportedAt->format('l, d F Y  H:i:s')]);
            fputcsv($file, ['Total Records',        $logs->count()]);
            fputcsv($file, ['Filter / Search',      $search ?: '-']);
            fputcsv($file, ['Date From',            $dateFrom ?: '-']);
            fputcsv($file, ['Date To',              $dateTo ?: '-']);
            fputcsv($file, ['Status Filter',        $statusLabel]);
            fputcsv($file, ['Gas Value Min (ppm)',  $gasMin ?? '-']);
            fputcsv($file, ['Gas Value Max (ppm)',  $gasMax ?? '-']);
            fputcsv($file, ['Gas Safety Threshold', '1500 ppm']);
            fputcsv($file, []); // blank row spacer

            // ─── Quick Statistics ─────────────────────────────────────────────────────
            $fireCount    = $logs->where('flame_detected', true)->count();
            $gasLeakCount = $logs->where('flame_detected', false)->filter(fn($l) => $l->gas_value > 1500)->count();
            $safeCount    = $logs->filter(fn($l) => !$l->flame_detected && $l->gas_value <= 1500)->count();
            $avgGas       = $logs->count() ? round($logs->avg('gas_value'), 2) : 0;
            $maxGas       = $logs->count() ? $logs->max('gas_value') : 0;
            $minGas       = $logs->count() ? $logs->min('gas_value') : 0;

            fputcsv($file, ['=== SUMMARY ===']);
            fputcsv($file, ['Total Safe Events',      $safeCount]);
            fputcsv($file, ['Total Gas Leak Events',  $gasLeakCount]);
            fputcsv($file, ['Total Fire Detected Events', $fireCount]);
            fputcsv($file, ['Average Gas Value (ppm)', $avgGas]);
            fputcsv($file, ['Maximum Gas Value (ppm)', $maxGas]);
            fputcsv($file, ['Minimum Gas Value (ppm)', $minGas]);
            fputcsv($file, []); // blank row spacer

            // ─── Column Headers ───────────────────────────────────────────────────────
            fputcsv($file, [
                'No.',
                'Record ID',
                'Gas Value (ppm)',
                'Gas Level',
                'Flame Sensor',
                'System Status',
                'Date',
                'Time',
                'Day of Week',
                'Timestamp (Full)',
            ]);

            // ─── Data Rows ────────────────────────────────────────────────────────────
            $rowNumber = 1;
            foreach ($logs as $log) {
                $gasValue      = (int) $log->gas_value;
                $flameDetected = (bool) $log->flame_detected;
                $timestamp     = Carbon::parse($log->created_at);

                // Derive gas level label
                if ($gasValue <= 400)       $gasLevel = 'Very Low';
                elseif ($gasValue <= 800)   $gasLevel = 'Low';
                elseif ($gasValue <= 1200)  $gasLevel = 'Moderate';
                elseif ($gasValue <= 1500)  $gasLevel = 'High';
                else                         $gasLevel = 'CRITICAL - Exceeds Threshold';

                // Derive system status (use $rowStatus to avoid overwriting outer $status filter variable)
                if ($flameDetected)          $rowStatus = 'FIRE DETECTED';
                elseif ($gasValue > 1500)    $rowStatus = 'GAS LEAK';
                else                         $rowStatus = 'SAFE';

                fputcsv($file, [
                    $rowNumber++,
                    $log->id,
                    $gasValue,
                    $gasLevel,
                    $flameDetected ? 'FIRE DETECTED' : 'Normal',
                    $rowStatus,
                    $timestamp->format('Y-m-d'),
                    $timestamp->format('H:i:s'),
                    $timestamp->format('l'),       // e.g. Monday
                    $timestamp->format('Y-m-d H:i:s'),
                ]);
            }

            // ─── Footer ───────────────────────────────────────────────────────────────
            fputcsv($file, []);
            fputcsv($file, ['--- End of Report ---']);
            fputcsv($file, ['Smart Safety IoT Monitoring System']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
