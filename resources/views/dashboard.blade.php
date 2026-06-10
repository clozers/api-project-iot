<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0">
            <div>
                <h2 class="font-semibold text-xl text-slate-100 leading-tight">
                    {{ __('Industrial IoT Monitoring Dashboard') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">Smart Safety System — ESP32 Sensor Nodes</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-xs text-slate-400">Communication Mode:</span>
                <span id="device-pill" class="px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 {{ $deviceInfo['online'] ? 'bg-emerald-950/50 text-emerald-400 border border-emerald-800' : 'bg-rose-950/50 text-rose-400 border border-rose-800' }}">
                    <span id="device-dot" class="h-2 w-2 rounded-full {{ $deviceInfo['online'] ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                    <span id="device-status-text">{{ $deviceInfo['status'] }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Custom CSS animations for Industrial IoT aesthetics -->
    <style>
        .glass-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(51, 65, 85, 0.4);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .glass-card:hover {
            border-color: rgba(71, 85, 105, 0.6);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pulse-border {
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-red {
            0% { border-color: rgba(239, 68, 68, 0.4); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { border-color: rgba(239, 68, 68, 0.8); box-shadow: 0 0 10px 4px rgba(239, 68, 68, 0.2); }
            100% { border-color: rgba(239, 68, 68, 0.4); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.0); }
        }
        .siren-glow {
            animation: siren 1s infinite alternate;
        }
        @keyframes siren {
            from { filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.2)); }
            to { filter: drop-shadow(0 0 12px rgba(239, 68, 68, 0.8)); }
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ================= ALERT BANNERS SYSTEM ================= -->
            <div id="alerts-container" class="space-y-4">
                <!-- Fire Detected Alert (Flashing Danger Banner) -->
                <div id="alert-fire" class="{{ ($latestLog && $latestLog->flame_detected) ? '' : 'hidden' }} flex items-center justify-between p-4 bg-red-900/80 border border-red-500 rounded-lg text-white font-bold animate-pulse shadow-lg siren-glow">
                    <div class="flex items-center space-x-3">
                        <svg class="h-6 w-6 text-red-100 siren-glow animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="text-sm tracking-wider uppercase bg-red-950 px-2 py-0.5 rounded mr-2">CRITICAL</span>
                            <span class="text-lg">FIRE DETECTED - KY-026 Flame Sensor triggered! Active Buzzer enabled on-site.</span>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <span class="text-xs uppercase bg-red-800 py-1 px-3 rounded-full border border-red-400">Evacuate Area</span>
                    </div>
                </div>

                <!-- Gas Leak Alert (Red Alert Banner) -->
                <div id="alert-gas" class="{{ ($latestLog && $latestLog->gas_value > 1500 && !$latestLog->flame_detected) ? '' : 'hidden' }} flex items-center justify-between p-4 bg-amber-900/80 border border-amber-500 rounded-lg text-white font-bold shadow-lg">
                    <div class="flex items-center space-x-3">
                        <svg class="h-6 w-6 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <div>
                            <span class="text-sm tracking-wider uppercase bg-amber-950 px-2 py-0.5 rounded mr-2">WARNING</span>
                            <span class="text-lg">GAS LEAK DETECTED - MQ-2 Gas Value exceeds threshold (>1500 ppm)! Vent room immediately.</span>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <span class="text-xs uppercase bg-amber-800 py-1 px-3 rounded-full border border-amber-400">Ventilate</span>
                    </div>
                </div>
            </div>

            <!-- ================= STATUS & SENSOR VALUES OVERVIEW ================= -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- System Status Panel -->
                <div id="status-panel" class="glass-card rounded-xl p-6 flex flex-col justify-between border-t-4 {{ $systemState['class'] }}">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Current System Status</div>
                        <div id="system-status-title" class="text-3xl font-extrabold mt-2 tracking-wide">{{ $systemState['status'] }}</div>
                    </div>
                    <p id="system-status-desc" class="text-sm mt-4 leading-relaxed opacity-90">
                        {{ $systemState['message'] }}
                    </p>
                    <div class="mt-6 pt-4 border-t border-slate-800/40 flex items-center justify-between text-xs text-slate-400">
                        <span>Check interval: 5 seconds</span>
                        <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Node Active</span>
                    </div>
                </div>

                <!-- Live MQ-2 Gas Sensor Panel -->
                <div class="glass-card rounded-xl p-6 flex flex-col justify-between text-slate-100">
                    <div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">MQ-2 Gas Sensor</span>
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold border border-slate-700 bg-slate-800 text-slate-400">PPM</span>
                        </div>
                        <div class="flex items-baseline mt-4">
                            <span id="live-gas-value" class="text-5xl font-black text-slate-100 tracking-tight">{{ $latestLog ? $latestLog->gas_value : '--' }}</span>
                            <span class="text-slate-400 text-lg ml-2 font-medium">ppm</span>
                        </div>
                    </div>
                    <div class="mt-6 space-y-2">
                        <div class="flex justify-between text-xs text-slate-400">
                            <span>Safety Threshold: 1500 ppm</span>
                            <span id="gas-level-pct">Avg: {{ $dailyStats['avg_gas'] }} ppm</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-700/50">
                            <div id="gas-progress" class="h-full rounded-full transition-all duration-500 ease-out {{ ($latestLog && $latestLog->gas_value > 1500) ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ min(100, (($latestLog ? $latestLog->gas_value : 0) / 2000) * 100) }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Live KY-026 Flame Sensor Panel -->
                <div class="glass-card rounded-xl p-6 flex flex-col justify-between text-slate-100">
                    <div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">KY-026 Flame Sensor</span>
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold border border-slate-700 bg-slate-800 text-slate-400">IR Diode</span>
                        </div>
                        <div class="flex items-center space-x-4 mt-4">
                            <div id="flame-icon-bg" class="p-3 rounded-lg flex items-center justify-center {{ ($latestLog && $latestLog->flame_detected) ? 'bg-red-950/40 text-red-500 border border-red-500/30 siren-glow' : 'bg-slate-800/40 text-slate-500 border border-slate-700/30' }}">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.996 7.996 0 0120 13a7.996 7.996 0 01-2.343 5.657z" />
                                </svg>
                            </div>
                            <div>
                                <div id="live-flame-text" class="text-2xl font-bold {{ ($latestLog && $latestLog->flame_detected) ? 'text-red-400' : 'text-slate-300' }}">
                                    {{ ($latestLog && $latestLog->flame_detected) ? 'FLAME DETECTED' : 'NO FLAME' }}
                                </div>
                                <div class="text-xs text-slate-400 mt-1">Status: Active Monitoring</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/60 flex justify-between text-xs text-slate-400">
                        <span>Hardware: Active Buzzer link</span>
                        <span id="flame-status-indicator" class="font-semibold {{ ($latestLog && $latestLog->flame_detected) ? 'text-red-400' : 'text-emerald-400' }}">{{ ($latestLog && $latestLog->flame_detected) ? '🚨 CRITICAL' : '✓ OK' }}</span>
                    </div>
                </div>
            </div>

            <!-- ================= HARDWARE DEVICE COMMUNICATIONS ================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Device Comm Status -->
                <div class="glass-card rounded-xl p-5 text-slate-100 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div id="device-comm-icon" class="p-3 rounded-lg flex items-center justify-center {{ $deviceInfo['online'] ? 'bg-emerald-950/40 text-emerald-400' : 'bg-rose-950/40 text-rose-400' }}">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a9 9 0 0112.728 0M1.929 7.929a13 13 0 0118.142 0" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Device Connection Status</div>
                            <div id="device-info-comm" class="text-lg font-bold mt-1 text-slate-200">
                                ESP32 Node: <span id="device-text-status" class="{{ $deviceInfo['online'] ? 'text-emerald-400' : 'text-rose-400' }}">{{ $deviceInfo['status'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] text-slate-400 uppercase font-semibold">Last Transmission</div>
                        <div id="device-last-seen" class="text-sm font-semibold mt-1 text-slate-300">{{ $deviceInfo['last_seen'] }}</div>
                    </div>
                </div>

                <!-- Device Session Uptime -->
                <div class="glass-card rounded-xl p-5 text-slate-100 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-indigo-950/40 text-indigo-400 rounded-lg flex items-center justify-center">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Active System Uptime</div>
                            <div id="device-uptime" class="text-lg font-bold mt-1 text-indigo-300">
                                {{ $deviceInfo['uptime'] }}
                            </div>
                        </div>
                    </div>
                    <div class="text-right text-xs text-slate-400 leading-snug">
                        Calculated by active<br>polling sequence
                    </div>
                </div>
            </div>

            <!-- ================= DYNAMIC STATISTICS CARDS ================= -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Records -->
                <div class="glass-card rounded-xl p-5 text-slate-100">
                    <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Records</div>
                    <div id="stat-total-records" class="text-3xl font-extrabold mt-2 text-slate-100">{{ $totalCount }}</div>
                    <div class="text-xs text-slate-400 mt-2 flex justify-between">
                        <span>Database Size</span>
                        <span>Log Size</span>
                    </div>
                </div>

                <!-- Latest Gas Value -->
                <div class="glass-card rounded-xl p-5 text-slate-100">
                    <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Latest Gas Level</div>
                    <div id="stat-latest-gas" class="text-3xl font-extrabold mt-2 text-slate-100">{{ $latestLog ? $latestLog->gas_value : '--' }} <span class="text-sm text-slate-400 font-normal">ppm</span></div>
                    <div class="text-xs mt-2 flex justify-between items-center">
                        <span class="text-slate-400">Current Value</span>
                        <span id="stat-latest-gas-status" class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ ($latestLog && $latestLog->gas_value > 1500) ? 'bg-amber-950 text-amber-400' : 'bg-emerald-950 text-emerald-400' }}">
                            {{ ($latestLog && $latestLog->gas_value > 1500) ? 'ELEVATED' : 'SAFE' }}
                        </span>
                    </div>
                </div>

                <!-- Flame Detection Count -->
                <div class="glass-card rounded-xl p-5 text-slate-100">
                    <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Fire Alerts Logged</div>
                    <div id="stat-flame-count" class="text-3xl font-extrabold mt-2 text-rose-500">{{ $flameCount }}</div>
                    <div class="text-xs text-slate-400 mt-2 flex justify-between">
                        <span>KY-026 Detections</span>
                        <span>Total Fire Count</span>
                    </div>
                </div>

                <!-- Last Communication Sync -->
                <div class="glass-card rounded-xl p-5 text-slate-100">
                    <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Last Sync Time</div>
                    <div id="stat-last-sync" class="text-lg font-bold mt-3.5 text-slate-200">{{ $latestLog ? \Carbon\Carbon::parse($latestLog->created_at)->format('H:i:s') : 'Never' }}</div>
                    <div class="text-xs text-slate-400 mt-3 flex justify-between">
                        <span>HH:MM:SS format</span>
                        <span>MySQL DB</span>
                    </div>
                </div>
            </div>

            <!-- ================= DAILY VS WEEKLY STATS GRID ================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Daily Stats -->
                <div class="glass-card rounded-xl p-5 text-slate-100">
                    <h3 class="text-sm font-bold text-slate-300 uppercase border-b border-slate-800 pb-3 flex justify-between items-center">
                        <span>Daily Statistics (Today)</span>
                        <span class="px-2 py-0.5 bg-slate-800 text-[10px] text-slate-400 rounded">24 Hours</span>
                    </h3>
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <div class="text-xs text-slate-400">Average Gas</div>
                            <div id="daily-avg-gas" class="text-xl font-bold mt-1 text-slate-100">{{ $dailyStats['avg_gas'] }} <span class="text-xs font-normal text-slate-400">ppm</span></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Peak Gas</div>
                            <div id="daily-max-gas" class="text-xl font-bold mt-1 text-slate-100">{{ $dailyStats['max_gas'] }} <span class="text-xs font-normal text-slate-400">ppm</span></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Fire Events</div>
                            <div id="daily-flame-count" class="text-xl font-bold mt-1 text-rose-500">{{ $dailyStats['flame_count'] }}</div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Stats -->
                <div class="glass-card rounded-xl p-5 text-slate-100">
                    <h3 class="text-sm font-bold text-slate-300 uppercase border-b border-slate-800 pb-3 flex justify-between items-center">
                        <span>Weekly Statistics (7 Days)</span>
                        <span class="px-2 py-0.5 bg-slate-800 text-[10px] text-slate-400 rounded">1 Week</span>
                    </h3>
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <div class="text-xs text-slate-400">Average Gas</div>
                            <div id="weekly-avg-gas" class="text-xl font-bold mt-1 text-slate-100">{{ $weeklyStats['avg_gas'] }} <span class="text-xs font-normal text-slate-400">ppm</span></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Peak Gas</div>
                            <div id="weekly-max-gas" class="text-xl font-bold mt-1 text-slate-100">{{ $weeklyStats['max_gas'] }} <span class="text-xs font-normal text-slate-400">ppm</span></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Fire Events</div>
                            <div id="weekly-flame-count" class="text-xl font-bold mt-1 text-rose-500">{{ $weeklyStats['flame_count'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= VISUAL CHARTS SECTION ================= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gas Level History Line Chart -->
                <div class="glass-card rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-800/80 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-200">Gas Level History</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Real-time tracker of the last 20 readings</p>
                        </div>
                        <span class="px-2 py-0.5 bg-emerald-950 text-emerald-400 text-[10px] uppercase font-bold border border-emerald-800 rounded">Live Feed</span>
                    </div>
                    <div class="h-80 relative">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>

                <!-- Daily Gas Average Chart -->
                <div class="glass-card rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-800/80 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-200">Daily Gas Average</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Average pollution values for the last 7 days</p>
                        </div>
                        <span class="px-2 py-0.5 bg-slate-800 text-slate-300 text-[10px] uppercase font-bold border border-slate-700 rounded">Daily Avg</span>
                    </div>
                    <div class="h-80 relative">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ================= RECENT READINGS LIST PREVIEW ================= -->
            <div class="glass-card rounded-xl p-6">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-800/80">
                    <div>
                        <h3 class="text-base font-bold text-slate-200">Recent Sensor Logs</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Latest 10 communication events processed</p>
                    </div>
                    <a href="{{ route('logs.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 hover:underline flex items-center space-x-1">
                        <span>Open Complete logs table</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs border-b border-slate-850">
                            <tr>
                                <th class="py-3 px-4 font-semibold">ID</th>
                                <th class="py-3 px-4 font-semibold">Gas Value</th>
                                <th class="py-3 px-4 font-semibold">Flame Status</th>
                                <th class="py-3 px-4 font-semibold">Timestamp</th>
                                <th class="py-3 px-4 font-semibold">Latency</th>
                            </tr>
                        </thead>
                        <tbody id="recent-logs-table" class="divide-y divide-slate-800/40">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-slate-800/25 transition">
                                    <td class="py-3.5 px-4 font-medium text-slate-400">#{{ $log->id }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-bold">{{ $log->gas_value }}</span>
                                            <span class="text-xs text-slate-500">ppm</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $log->flame_detected ? 'bg-red-950/40 text-red-400 border border-red-500/20' : 'bg-emerald-950/40 text-emerald-400 border border-emerald-500/20' }}">
                                            {{ $log->flame_detected ? 'FIRE DETECTED' : 'SAFE' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-400 text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-3.5 px-4 text-slate-500 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">No sensor log events available in MySQL database.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ================= REAL-TIME MONITORING SCRIPTS & CHART.JS ================= -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart.js Default styling overrides for dark theme
            Chart.defaults.color = '#94a3b8'; // text-slate-400
            Chart.defaults.borderColor = 'rgba(51, 65, 85, 0.2)'; // border-slate-700/20

            // 1. Initialize Line Chart (Gas History)
            const ctxLine = document.getElementById('lineChart').getContext('2d');
            
            // Set up gradients
            const gasGradient = ctxLine.createLinearGradient(0, 0, 0, 300);
            gasGradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); // bg-emerald-500
            gasGradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            const initialLineLabels = {!! json_encode($historyLogs->map(fn($log) => \Carbon\Carbon::parse($log->created_at)->format('H:i:s'))->toArray()) !!};
            const initialLineData = {!! json_encode($historyLogs->pluck('gas_value')->toArray()) !!};

            const lineChart = new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: initialLineLabels,
                    datasets: [{
                        label: 'Gas Level (ppm)',
                        data: initialLineData,
                        borderColor: '#10b981', // emerald-500
                        borderWidth: 2,
                        fill: true,
                        backgroundColor: gasGradient,
                        tension: 0.3,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#0f172a', // dark border
                        pointHoverBackgroundColor: '#34d399',
                        pointHoverBorderColor: '#0f172a',
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(51, 65, 85, 0.15)' },
                            title: { display: true, text: 'ppm' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // 2. Initialize Bar Chart (Daily averages)
            const ctxBar = document.getElementById('barChart').getContext('2d');
            
            const initialBarLabels = {!! json_encode($dailyAverages->pluck('label')->toArray()) !!};
            const initialBarData = {!! json_encode($dailyAverages->pluck('avg_gas')->toArray()) !!};

            const barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: initialBarLabels,
                    datasets: [{
                        label: 'Daily Average',
                        data: initialBarData,
                        backgroundColor: '#6366f1', // indigo-500
                        borderRadius: 4,
                        maxBarThickness: 32,
                        hoverBackgroundColor: '#818cf8',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(51, 65, 85, 0.15)' },
                            title: { display: true, text: 'Average (ppm)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // 3. Real-Time AJAX/Fetch Polling Functionality
            function pollSensorData() {
                fetch('{{ route("dashboard.data") }}')
                    .then(response => response.json())
                    .then(data => {
                        // A. Update Device comm status
                        const pill = document.getElementById('device-pill');
                        const dot = document.getElementById('device-dot');
                        const text = document.getElementById('device-status-text');
                        const commText = document.getElementById('device-text-status');
                        const commIcon = document.getElementById('device-comm-icon');
                        
                        if (data.device_info.online) {
                            pill.className = "px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 bg-emerald-950/50 text-emerald-400 border border-emerald-800";
                            dot.className = "h-2 w-2 rounded-full bg-emerald-400 animate-pulse";
                            text.textContent = "ONLINE";
                            commText.className = "text-emerald-400 font-bold";
                            commText.textContent = "ONLINE";
                            commIcon.className = "p-3 rounded-lg flex items-center justify-center bg-emerald-950/40 text-emerald-400";
                        } else {
                            pill.className = "px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 bg-rose-950/50 text-rose-400 border border-rose-800";
                            dot.className = "h-2 w-2 rounded-full bg-rose-400";
                            text.textContent = "OFFLINE";
                            commText.className = "text-rose-400 font-bold";
                            commText.textContent = "OFFLINE";
                            commIcon.className = "p-3 rounded-lg flex items-center justify-center bg-rose-950/40 text-rose-400";
                        }
                        
                        document.getElementById('device-last-seen').textContent = data.device_info.last_seen;
                        document.getElementById('device-uptime').textContent = data.device_info.uptime;

                        // B. Update Alert Banners
                        const alertFire = document.getElementById('alert-fire');
                        const alertGas = document.getElementById('alert-gas');

                        if (data.latest_log) {
                            if (data.latest_log.flame_detected) {
                                alertFire.classList.remove('hidden');
                                alertGas.classList.add('hidden'); // Prioritize fire alert
                            } else {
                                alertFire.classList.add('hidden');
                                if (data.latest_log.gas_value > 1500) {
                                    alertGas.classList.remove('hidden');
                                } else {
                                    alertGas.classList.add('hidden');
                                }
                            }
                        }

                        // C. Update System Status panel
                        const panel = document.getElementById('status-panel');
                        const statusTitle = document.getElementById('system-status-title');
                        const statusDesc = document.getElementById('system-status-desc');
                        
                        panel.className = `glass-card rounded-xl p-6 flex flex-col justify-between border-t-4 ${data.system_state.class}`;
                        statusTitle.textContent = data.system_state.status;
                        statusDesc.textContent = data.system_state.message;

                        // D. Update Live reading panels
                        const gasVal = data.latest_log ? data.latest_log.gas_value : '--';
                        document.getElementById('live-gas-value').textContent = gasVal;
                        
                        const gasProgress = document.getElementById('gas-progress');
                        const gasPct = data.latest_log ? Math.min(100, (data.latest_log.gas_value / 2000) * 100) : 0;
                        gasProgress.style.width = `${gasPct}%`;
                        if (data.latest_log && data.latest_log.gas_value > 1500) {
                            gasProgress.className = "h-full rounded-full bg-amber-500 transition-all duration-500 ease-out";
                        } else {
                            gasProgress.className = "h-full rounded-full bg-emerald-500 transition-all duration-500 ease-out";
                        }
                        document.getElementById('gas-level-pct').textContent = `Avg: ${data.stats.daily.avg_gas} ppm`;

                        // Flame panel updates
                        const flameText = document.getElementById('live-flame-text');
                        const flameBg = document.getElementById('flame-icon-bg');
                        const flameStatusText = document.getElementById('flame-status-indicator');

                        if (data.latest_log && data.latest_log.flame_detected) {
                            flameText.textContent = "FLAME DETECTED";
                            flameText.className = "text-2xl font-bold text-red-400";
                            flameBg.className = "p-3 rounded-lg flex items-center justify-center bg-red-950/40 text-red-500 border border-red-500/30 siren-glow";
                            flameStatusText.textContent = "🚨 CRITICAL";
                            flameStatusText.className = "font-semibold text-red-400";
                        } else {
                            flameText.textContent = "NO FLAME";
                            flameText.className = "text-2xl font-bold text-slate-300";
                            flameBg.className = "p-3 rounded-lg flex items-center justify-center bg-slate-800/40 text-slate-500 border border-slate-700/30";
                            flameStatusText.textContent = "✓ OK";
                            flameStatusText.className = "font-semibold text-emerald-400";
                        }

                        // E. Update Stats cards values
                        document.getElementById('stat-total-records').textContent = data.stats.total_records;
                        document.getElementById('stat-latest-gas').innerHTML = `${gasVal} <span class="text-sm text-slate-400 font-normal">ppm</span>`;
                        document.getElementById('stat-flame-count').textContent = data.stats.flame_count;
                        document.getElementById('stat-last-sync').textContent = data.latest_log ? data.latest_log.timestamp.split(' ')[1] : 'Never';

                        const statGasStatus = document.getElementById('stat-latest-gas-status');
                        if (data.latest_log && data.latest_log.gas_value > 1500) {
                            statGasStatus.textContent = 'ELEVATED';
                            statGasStatus.className = 'px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-950 text-amber-400';
                        } else {
                            statGasStatus.textContent = 'SAFE';
                            statGasStatus.className = 'px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-950 text-emerald-400';
                        }

                        // F. Update Daily/Weekly stats tables
                        document.getElementById('daily-avg-gas').innerHTML = `${data.stats.daily.avg_gas} <span class="text-xs font-normal text-slate-400">ppm</span>`;
                        document.getElementById('daily-max-gas').innerHTML = `${data.stats.daily.max_gas} <span class="text-xs font-normal text-slate-400">ppm</span>`;
                        document.getElementById('daily-flame-count').textContent = data.stats.daily.flame_count;

                        document.getElementById('weekly-avg-gas').innerHTML = `${data.stats.weekly.avg_gas} <span class="text-xs font-normal text-slate-400">ppm</span>`;
                        document.getElementById('weekly-max-gas').innerHTML = `${data.stats.weekly.max_gas} <span class="text-xs font-normal text-slate-400">ppm</span>`;
                        document.getElementById('weekly-flame-count').textContent = data.stats.weekly.flame_count;

                        // G. Update Recent logs table
                        const logsTable = document.getElementById('recent-logs-table');
                        if (data.recent_logs.length === 0) {
                            logsTable.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-500">No sensor log events available in MySQL database.</td></tr>`;
                        } else {
                            let tableHtml = '';
                            data.recent_logs.forEach(log => {
                                const flamePill = log.flame_detected 
                                    ? `<span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-950/40 text-red-400 border border-red-500/20">FIRE DETECTED</span>`
                                    : `<span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-950/40 text-emerald-400 border border-emerald-500/20">SAFE</span>`;
                                
                                tableHtml += `
                                    <tr class="hover:bg-slate-800/25 transition">
                                        <td class="py-3.5 px-4 font-medium text-slate-400">#${log.id}</td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-bold">${log.gas_value}</span>
                                                <span class="text-xs text-slate-500">ppm</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">${flamePill}</td>
                                        <td class="py-3.5 px-4 text-slate-400 text-xs">${log.timestamp}</td>
                                        <td class="py-3.5 px-4 text-slate-500 text-xs">${log.time_ago}</td>
                                    </tr>
                                `;
                            });
                            logsTable.innerHTML = tableHtml;
                        }

                        // H. Update line chart data dynamically
                        lineChart.data.labels = data.charts.line.labels;
                        lineChart.data.datasets[0].data = data.charts.line.gas_values;
                        
                        // Update grid color depending on current reading to flash red border if in danger
                        if (data.latest_log && (data.latest_log.flame_detected || data.latest_log.gas_value > 1500)) {
                            lineChart.data.datasets[0].borderColor = '#f43f5e'; // rose-500
                            lineChart.data.datasets[0].pointBackgroundColor = '#f43f5e';
                        } else {
                            lineChart.data.datasets[0].borderColor = '#10b981'; // emerald-500
                            lineChart.data.datasets[0].pointBackgroundColor = '#10b981';
                        }
                        lineChart.update();

                        // I. Update bar chart averages
                        barChart.data.labels = data.charts.bar.labels;
                        barChart.data.datasets[0].data = data.charts.bar.averages;
                        barChart.update();
                    })
                    .catch(err => console.error("Error fetching live IoT data stream: ", err));
            }

            // Trigger poll every 5 seconds (5000 milliseconds)
            setInterval(pollSensorData, 5000);
        });
    </script>
</x-app-layout>
