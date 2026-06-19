<?php

return [
    'default' => env('DB_DRIVER', 'mysql'),
    'time_query_rule' => [],
    'auto_timestamp' => true,
    'datetime_format' => 'Y-m-d H:i:s',
    'datetime_field' => '',

    'connections' => [
        'mysql' => [
            'type' => env('DB_TYPE', 'mysql'),
            'hostname' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_NAME', 'xos_piksell_cn'),
            'username' => env('DB_USER', 'xos_piksell_cn'),
            'password' => env('DB_PASS', 'xos_piksell_cn'),
            'hostport' => env('DB_PORT', '3306'),
            'params' => [],
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'prefix' => env('DB_PREFIX', ''),
            'deploy' => 0,
            'rw_separate' => false,
            'master_num' => 1,
            'slave_no' => '',
            'fields_strict' => true,
            'break_reconnect' => false,
            'trigger_sql' => env('APP_DEBUG', false),
            'fields_cache' => false,
        ],
        'sqlite' => [
            'type' => 'sqlite',
            'database' => env('DB_NAME', root_path() . 'database/xos_piksell_cn.db'),
            'prefix' => env('DB_PREFIX', ''),
            'deploy' => 0,
            'rw_separate' => false,
            'master_num' => 1,
            'slave_no' => '',
            'fields_strict' => true,
            'break_reconnect' => false,
            'trigger_sql' => env('APP_DEBUG', false),
            'fields_cache' => false,
        ],
    ],
];

