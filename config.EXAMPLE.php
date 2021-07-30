<?php // ЮТФ-8

$conf['db'] = [
    'host' => '127.0.0.1', // server name
    'base' => '', //database name
    'user' => '', // user's login
    'password' => '', // user's password
    'pref' => 'dc',
    'charset' => 'utf8mb4',
    'collate' => 'utf8mb4_unicode_ci',
];

$_SESSION['baseurl'] = realpath(dirname(__FILE__)) . '/';

$dbgames = 'games';
$dbgrids = 'grids';
$dbcells = 'cells';
$dblog = 'log';
