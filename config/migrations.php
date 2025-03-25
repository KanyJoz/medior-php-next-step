<?php

declare(strict_types=1);

// We need the path_constants to use the MIGRATIONS_PATH
require_once 'path_constants.php';

return [
    'table_storage' => [
        'table_name'                 => 'migrations',
        'version_column_name'        => 'version',
        'version_column_length'      => 255,
        'executed_at_column_name'    => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],
    'migrations_paths' => [
        'Migrations' => MIGRATIONS_PATH,
    ],
    'all_or_nothing'          => true,
    'transactional'           => true,
    'check_database_platform' => true,
    'organize_migrations'     => 'none',
    'connection'              => null,
    'em'                      => null,
];
