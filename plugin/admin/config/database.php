<?php
return  [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => '122.114.74.62',
            'port'        => '3306',
            'database'    => '0303jietiao_62_h',
            'username'    => '0303jietiao_62_h',
            'password'    => 'WjQaaGyhA5hBzDj2',
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => true, // Must be false for Swoole and Swow drivers.
            ],
        ],
    ],
];