<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SensorLog;
use Carbon\Carbon;

class SensorLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing logs
        SensorLog::truncate();

        $now = Carbon::now();
        $logs = [];

        // Seed logs for the past 7 days (day 6 to day 1 ago)
        for ($i = 6; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Seed 20 readings per day
            for ($j = 0; $j < 20; $j++) {
                // Adjust hours
                $logTime = $date->copy()->startOfDay()->addHours($j * 1.2)->addMinutes(rand(0, 10));
                
                // Determine if this reading is a simulated anomaly
                $isAnomalyDay = ($i === 3 || $i === 1); // Anomaly on day 3 and day 1 ago
                $isGasLeak = $isAnomalyDay && ($j >= 10 && $j <= 12);
                $isFire = $isAnomalyDay && ($j === 11);

                if ($isFire) {
                    $gasValue = rand(1600, 2200);
                    $flameDetected = true;
                } elseif ($isGasLeak) {
                    $gasValue = rand(1550, 1800);
                    $flameDetected = false;
                } else {
                    // Normal behavior: typical clean-air baseline between 200 and 450
                    $gasValue = rand(200, 450);
                    $flameDetected = false;
                }

                $logs[] = [
                    'gas_value' => $gasValue,
                    'flame_detected' => $flameDetected,
                    'created_at' => $logTime,
                    'updated_at' => $logTime,
                ];
            }
        }

        // Seed logs for today (up to 30 logs, more frequent, every 30 mins, up to 15 mins ago)
        $today = Carbon::today();
        for ($h = 0; $h < 40; $h++) {
            $logTime = $today->copy()->addMinutes($h * 20);
            
            // Don't seed future logs
            if ($logTime->greaterThan($now)) {
                break;
            }

            // Normal values for today
            $gasValue = rand(220, 480);
            $flameDetected = false;

            // Let's add a short gas anomaly today around 8:00 AM
            if ($logTime->hour === 8 && ($logTime->minute >= 0 && $logTime->minute <= 40)) {
                $gasValue = rand(1550, 1750);
            }

            $logs[] = [
                'gas_value' => $gasValue,
                'flame_detected' => $flameDetected,
                'created_at' => $logTime,
                'updated_at' => $logTime,
            ];
        }

        // Add the very latest active reading (5 seconds ago) to show live status
        $logs[] = [
            'gas_value' => 310, // Safe baseline
            'flame_detected' => false,
            'created_at' => Carbon::now()->subSeconds(5),
            'updated_at' => Carbon::now()->subSeconds(5),
        ];

        // Bulk insert logs
        foreach (array_chunk($logs, 100) as $chunk) {
            SensorLog::insert($chunk);
        }

        $this->command->info('Sensor logs seeded successfully! Total logs: ' . count($logs));
    }
}
