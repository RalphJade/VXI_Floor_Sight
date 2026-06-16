<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VXI FloorSight Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all settings specific to the VXI
    | FloorSight application for IT Asset Management.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | IP Whitelisting
    |--------------------------------------------------------------------------
    |
    | Allowed network subnets for accessing the application. Uses CIDR notation.
    | All requests from non-whitelisted IPs will be rejected with 403 Forbidden.
    |
    */
    'allowed_subnets' => [
        '192.168.0.0/16',      // Internal LAN
        '10.0.0.0/8',          // Private network
        '172.16.0.0/12',       // Private network range
        '127.0.0.1',           // Localhost for development/testing
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Desktop Protocol
    |--------------------------------------------------------------------------
    |
    | Protocol used for launching remote sessions.
    | Options: 'rdp' (Windows), 'vnc', 'ssh'
    |
    */
    'remote_protocol' => env('REMOTE_PROTOCOL', 'rdp'),

    /*
    |--------------------------------------------------------------------------
    | LDAP / Active Directory Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for LDAP/AD integration for syncing computer and user objects.
    | This is used for automated workstation discovery and management.
    |
    */
    'ldap' => [
        'enabled' => env('LDAP_ENABLED', false),
        'hosts' => explode(',', env('LDAP_HOSTS', 'ldap.company.local')),
        'port' => env('LDAP_PORT', 389),
        'base_dn' => env('LDAP_BASE_DN', 'dc=company,dc=local'),
        'username' => env('LDAP_USERNAME', 'service_account@company.local'),
        'password' => env('LDAP_PASSWORD', ''),

        // OU paths for syncing different floor/bay groups
        'computer_ous' => [
            'floor_1' => 'ou=Floor1,ou=BPO,dc=company,dc=local',
            'floor_2' => 'ou=Floor2,ou=BPO,dc=company,dc=local',
            'floor_3' => 'ou=Floor3,ou=BPO,dc=company,dc=local',
            // ... add more as needed
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    |
    | Real-time update intervals and display settings.
    |
    */
    'dashboard' => [
        'status_poll_interval' => env('STATUS_POLL_INTERVAL', 5000), // milliseconds
        'max_search_results' => env('MAX_SEARCH_RESULTS', 20),
        'items_per_page' => env('ITEMS_PER_PAGE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Audit trail configuration for compliance and tracking.
    |
    */
    'audit' => [
        'enabled' => env('AUDIT_ENABLED', true),
        'log_retention_days' => env('AUDIT_RETENTION_DAYS', 365),
        'track_ip' => true,
        'track_user_agent' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Building Configuration
    |--------------------------------------------------------------------------
    |
    | Physical building layout and organizational structure.
    |
    */
    'building' => [
        'name' => env('BUILDING_NAME', 'BPO Call Center'),
        'total_floors' => env('TOTAL_FLOORS', 12),
        'bays_per_floor' => env('BAYS_PER_FLOOR', 4),
        'seats_per_bay' => env('SEATS_PER_BAY', 25),
    ],

    /*
    |--------------------------------------------------------------------------
    | Network Monitoring
    |--------------------------------------------------------------------------
    |
    | Workstation health check and monitoring settings.
    |
    */
    'monitoring' => [
        'enabled' => env('MONITORING_ENABLED', true),
        'ping_interval_seconds' => env('PING_INTERVAL', 60),
        'ping_timeout_seconds' => env('PING_TIMEOUT', 5),
        'offline_threshold_pings' => env('OFFLINE_THRESHOLD', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific features of the application.
    |
    */
    'features' => [
        'ldap_sync' => env('FEATURE_LDAP_SYNC', false),
        'remote_sessions' => env('FEATURE_REMOTE_SESSIONS', true),
        'audit_logging' => env('FEATURE_AUDIT_LOGGING', true),
        'real_time_updates' => env('FEATURE_REAL_TIME_UPDATES', true),
        'advanced_search' => env('FEATURE_ADVANCED_SEARCH', true),
    ],
];
