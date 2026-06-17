<?php

return [
    // Navigasi
    'dashboard'             => 'Dashboard',
    'sensor_logs'           => 'Log Sensor',
    'profile'               => 'Profil',
    'log_out'               => 'Keluar',

    // Header dashboard
    'dashboard_title'       => 'Dashboard Monitoring IoT Industri',
    'smart_safety_system'   => 'Sistem Keamanan Cerdas — Node Sensor ESP32',
    'comm_mode'             => 'Mode Komunikasi:',

    // Banner peringatan
    'alert_fire_critical'   => 'KRITIS',
    'alert_fire_text'       => 'API TERDETEKSI - Sensor Api KY-026 aktif! Buzzer aktif di lokasi.',
    'alert_fire_action'     => 'Evakuasi Area',
    'alert_gas_warning'     => 'PERINGATAN',
    'alert_gas_text'        => 'KEBOCORAN GAS TERDETEKSI - Nilai Gas MQ-2 melebihi ambang batas (>1500 ppm)! Segera ventilasi ruangan.',
    'alert_gas_action'      => 'Ventilasi',

    // Panel status sistem
    'current_system_status' => 'Status Sistem Saat Ini',
    'check_interval'        => 'Interval pengecekan: 5 detik',
    'node_active'           => 'Node Aktif',

    // Panel sensor
    'mq2_sensor'            => 'Sensor Gas MQ-2',
    'ky026_sensor'          => 'Sensor Api KY-026',
    'ir_diode'              => 'Dioda IR',
    'safety_threshold'      => 'Ambang Batas Aman: 1500 ppm',
    'flame_detected_text'   => 'API TERDETEKSI',
    'no_flame'              => 'TIDAK ADA API',
    'status_active_mon'     => 'Status: Monitoring Aktif',
    'hardware_buzzer'       => 'Perangkat: Link Buzzer Aktif',

    // Status perangkat
    'device_conn_status'    => 'Status Koneksi Perangkat',
    'last_transmission'     => 'Transmisi Terakhir',
    'active_uptime'         => 'Uptime Sistem Aktif',
    'polling_sequence'      => 'Dihitung berdasarkan urutan polling aktif',

    // Kartu statistik
    'total_records'         => 'Total Data',
    'database_size'         => 'Ukuran Database',
    'log_size'              => 'Ukuran Log',
    'latest_gas_level'      => 'Kadar Gas Terkini',
    'current_value'         => 'Nilai Saat Ini',
    'fire_alerts_logged'    => 'Peringatan Api Tercatat',
    'ky026_detections'      => 'Deteksi KY-026',
    'total_fire_count'      => 'Total Kejadian Api',
    'last_sync_time'        => 'Waktu Sinkronisasi Terakhir',
    'hhmmss_format'         => 'Format HH:MM:SS',

    // Statistik harian/mingguan
    'daily_stats'           => 'Statistik Harian (Hari Ini)',
    'weekly_stats'          => 'Statistik Mingguan (7 Hari)',
    '24_hours'              => '24 Jam',
    '1_week'                => '1 Minggu',
    'average_gas'           => 'Rata-rata Gas',
    'peak_gas'              => 'Gas Tertinggi',
    'fire_events'           => 'Kejadian Api',

    // Grafik
    'gas_level_history'     => 'Riwayat Kadar Gas',
    'realtime_last20'       => 'Pelacak real-time 20 pembacaan terakhir',
    'live_feed'             => 'Siaran Langsung',
    'daily_gas_average'     => 'Rata-rata Gas Harian',
    'avg_last7days'         => 'Nilai rata-rata polusi 7 hari terakhir',
    'daily_avg'             => 'Rata-rata Harian',

    // Tabel log terkini
    'recent_sensor_logs'    => 'Log Sensor Terkini',
    'latest10_events'       => '10 event komunikasi terbaru yang diproses',
    'open_full_logs'        => 'Buka Tabel Log Lengkap',
    'gas_value'             => 'Nilai Gas',
    'flame_status'          => 'Status Api',
    'timestamp'             => 'Waktu',
    'latency'               => 'Latensi',
    'no_events_available'   => 'Tidak ada event log sensor tersedia di database MySQL.',

    // Halaman logs
    'sensor_comm_logs'      => 'Log Komunikasi Sensor',
    'logs_subtitle'         => 'Cari, urutkan, analisis, dan ekspor riwayat pembacaan MQ-2 dan KY-026',
    'export_csv'            => 'Ekspor CSV',
    'export_filter_options' => 'Opsi Filter Ekspor',
    'date_from'             => 'Tanggal Mulai',
    'date_to'               => 'Tanggal Selesai',
    'system_status'         => 'Status Sistem',
    'all_status'            => 'Semua Status',
    'safe_status'           => '✅ Aman (Safe)',
    'gas_leak_status'       => '⚠️ Kebocoran Gas',
    'fire_status'           => '🔥 Api Terdeteksi',
    'gas_min'               => 'Gas Minimum (ppm)',
    'gas_max'               => 'Gas Maksimum (ppm)',
    'download_csv'          => 'Unduh CSV',
    'apply_filter'          => 'Terapkan Filter',
    'clear'                 => 'Hapus',
    'search_placeholder'    => "Cari berdasarkan nilai ppm, ID, atau status api ('safe', 'fire', 'true')...",
    'no_logs_found'         => 'Tidak ada log sensor yang cocok dengan kriteria pencarian di database MySQL.',
    'threshold_exceeded'    => 'MELEBIHI BATAS',
    'safe'                  => 'AMAN',
    'fire_detected'         => 'API TERDETEKSI',

    // Nilai status
    'online'                => 'ONLINE',
    'offline'               => 'OFFLINE',
    'elevated'              => 'ELEVATED',
    'critical'              => '🚨 KRITIS',
    'ok'                    => '✓ OK',
];
