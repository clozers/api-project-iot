<?php

return [
    // Navigation
    'dashboard'             => 'Dashboard',
    'sensor_logs'           => 'Sensor Logs',
    'profile'               => 'Profile',
    'log_out'               => 'Log Out',

    // Dashboard header
    'dashboard_title'       => 'Industrial IoT Monitoring Dashboard',
    'smart_safety_system'   => 'Smart Safety System — ESP32 Sensor Nodes',
    'comm_mode'             => 'Communication Mode:',

    // Alert banners
    'alert_fire_critical'   => 'CRITICAL',
    'alert_fire_text'       => 'FIRE DETECTED - KY-026 Flame Sensor triggered! Active Buzzer enabled on-site.',
    'alert_fire_action'     => 'Evacuate Area',
    'alert_gas_warning'     => 'WARNING',
    'alert_gas_text'        => 'GAS LEAK DETECTED - MQ-2 Gas Value exceeds threshold (>1500 ppm)! Vent room immediately.',
    'alert_gas_action'      => 'Ventilate',

    // System status panel
    'current_system_status' => 'Current System Status',
    'check_interval'        => 'Check interval: 5 seconds',
    'node_active'           => 'Node Active',

    // Sensor panels
    'mq2_sensor'            => 'MQ-2 Gas Sensor',
    'ky026_sensor'          => 'KY-026 Flame Sensor',
    'ir_diode'              => 'IR Diode',
    'safety_threshold'      => 'Safety Threshold: 1500 ppm',
    'flame_detected_text'   => 'FLAME DETECTED',
    'no_flame'              => 'NO FLAME',
    'status_active_mon'     => 'Status: Active Monitoring',
    'hardware_buzzer'       => 'Hardware: Active Buzzer link',

    // Device status
    'device_conn_status'    => 'Device Connection Status',
    'last_transmission'     => 'Last Transmission',
    'active_uptime'         => 'Active System Uptime',
    'polling_sequence'      => 'Calculated by active polling sequence',

    // Stats cards
    'total_records'         => 'Total Records',
    'database_size'         => 'Database Size',
    'log_size'              => 'Log Size',
    'latest_gas_level'      => 'Latest Gas Level',
    'current_value'         => 'Current Value',
    'fire_alerts_logged'    => 'Fire Alerts Logged',
    'ky026_detections'      => 'KY-026 Detections',
    'total_fire_count'      => 'Total Fire Count',
    'last_sync_time'        => 'Last Sync Time',
    'hhmmss_format'         => 'HH:MM:SS format',

    // Daily/weekly stats
    'daily_stats'           => 'Daily Statistics (Today)',
    'weekly_stats'          => 'Weekly Statistics (7 Days)',
    '24_hours'              => '24 Hours',
    '1_week'                => '1 Week',
    'average_gas'           => 'Average Gas',
    'peak_gas'              => 'Peak Gas',
    'fire_events'           => 'Fire Events',

    // Charts
    'gas_level_history'     => 'Gas Level History',
    'realtime_last20'       => 'Real-time tracker of the last 20 readings',
    'live_feed'             => 'Live Feed',
    'daily_gas_average'     => 'Daily Gas Average',
    'avg_last7days'         => 'Average pollution values for the last 7 days',
    'daily_avg'             => 'Daily Avg',

    // Recent logs table
    'recent_sensor_logs'    => 'Recent Sensor Logs',
    'latest10_events'       => 'Latest 10 communication events processed',
    'open_full_logs'        => 'Open Complete logs table',
    'gas_value'             => 'Gas Value',
    'flame_status'          => 'Flame Status',
    'timestamp'             => 'Timestamp',
    'latency'               => 'Latency',
    'no_events_available'   => 'No sensor log events available in MySQL database.',

    // Logs page
    'sensor_comm_logs'      => 'Sensor Communication Logs',
    'logs_subtitle'         => 'Search, sort, analyze, and export historical MQ-2 and KY-026 readings',
    'export_csv'            => 'Export CSV',
    'export_filter_options' => 'Export Filter Options',
    'date_from'             => 'Start Date',
    'date_to'               => 'End Date',
    'system_status'         => 'System Status',
    'all_status'            => 'All Status',
    'safe_status'           => '✅ Safe',
    'gas_leak_status'       => '⚠️ Gas Leak',
    'fire_status'           => '🔥 Fire Detected',
    'gas_min'               => 'Gas Minimum (ppm)',
    'gas_max'               => 'Gas Maximum (ppm)',
    'download_csv'          => 'Download CSV',
    'apply_filter'          => 'Apply Filter',
    'clear'                 => 'Clear',
    'search_placeholder'    => "Search by Gas ppm value, ID, or flame status ('safe', 'fire', 'true')...",
    'no_logs_found'         => 'No sensor log events match the query criteria in MySQL database.',
    'threshold_exceeded'    => 'THRESHOLD EXCEEDED',
    'safe'                  => 'SAFE',
    'fire_detected'         => 'FIRE DETECTED',

    // Status values
    'online'                => 'ONLINE',
    'offline'               => 'OFFLINE',
    'elevated'              => 'ELEVATED',
    'critical'              => '🚨 CRITICAL',
    'ok'                    => '✓ OK',
];
