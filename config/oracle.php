<?php

return [
    'oracle' => [
        'driver'         => 'oracle',
        'tns'            => env('DB_TNS_ORACLE', ''),
        'host'           => env('DB_HOST_ORACLE', ''),
        'port'           => env('DB_PORT_ORACLE', '1521'),
        'database'       => env('DB_DATABASE_ORACLE', ''),
        'service_name'   => env('DB_SERVICE_NAME_ORACLE', ''),
        'username'       => env('DB_USERNAME_ORACLE', ''),
        'password'       => env('DB_PASSWORD_ORACLE', ''),
        'charset'        => env('DB_CHARSET_ORACLE', 'AL32UTF8'),
        'prefix'         => env('DB_PREFIX_ORACLE', ''),
        'prefix_schema'  => env('DB_SCHEMA_PREFIX_ORACLE', ''),
        'edition'        => env('DB_EDITION_ORACLE', 'ora$base'),
        'server_version' => env('DB_SERVER_VERSION_ORACLE', '11g'),
        'load_balance'   => env('DB_LOAD_BALANCE_ORACLE', 'yes'),
        'max_name_len'   => env('ORA_MAX_NAME_LEN_ORACLE', 30),
        'dynamic'        => [],
    ],
    'sessionVars' => [
        'NLS_TIME_FORMAT'         => 'HH24:MI:SS',
        'NLS_DATE_FORMAT'         => 'YYYY-MM-DD HH24:MI:SS',
        'NLS_TIMESTAMP_FORMAT'    => 'YYYY-MM-DD HH24:MI:SS',
        'NLS_TIMESTAMP_TZ_FORMAT' => 'YYYY-MM-DD HH24:MI:SS TZH:TZM',
        'NLS_NUMERIC_CHARACTERS'  => '.,',
    ],
];
